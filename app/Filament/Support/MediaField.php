<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaField
{
    /**
     * Normalize stored filename/URL into a path Filament FileUpload can preview from disk('public').
     */
    public static function toDiskPath(?string $value, string $directory): ?string
    {
        if (blank($value) || filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        $path = str_contains($value, '/') ? ltrim($value, '/') : "{$directory}/{$value}";

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    /**
     * Build a single FileUpload configured to store basenames inside storage/app/public/{directory}
     * while preserving external URLs when left unchanged.
     */
    public static function singleUpload(string $field, string $directory, int $maxSizeKb = 4096): FileUpload
    {
        return FileUpload::make($field)
            ->disk('public')
            ->directory($directory)
            ->image()
            ->imageEditor()
            ->maxSize($maxSizeKb)
            ->formatStateUsing(fn (?string $state): ?string => self::toDiskPath($state, $directory))
            ->dehydrateStateUsing(function (?string $state, ?Model $record) use ($field): ?string {
                if (filled($state)) {
                    return basename($state);
                }

                $existing = $record?->{$field};

                return ($existing && filter_var($existing, FILTER_VALIDATE_URL)) ? $existing : null;
            });
    }

    /**
     * Build a multiple-image FileUpload configured to store basename arrays inside storage/app/public/{directory}
     * while preserving external URL entries on existing records.
     */
    public static function multipleUpload(string $field, string $directory, int $maxSizeKb = 4096): FileUpload
    {
        return FileUpload::make($field)
            ->disk('public')
            ->directory($directory)
            ->image()
            ->multiple()
            ->reorderable()
            ->maxSize($maxSizeKb)
            ->formatStateUsing(function (mixed $state) use ($directory): array {
                if (! is_array($state)) {
                    return [];
                }

                return collect($state)
                    ->map(fn ($item) => is_string($item) ? self::toDiskPath($item, $directory) : null)
                    ->filter()
                    ->values()
                    ->all();
            })
            ->dehydrateStateUsing(function (mixed $state, ?Model $record) use ($field): array {
                $localFiles = collect(is_array($state) ? $state : [])
                    ->filter(fn ($item) => filled($item) && is_string($item))
                    ->map(fn (string $item) => basename($item))
                    ->values();

                $existingUrls = collect(is_array($record?->{$field}) ? $record->{$field} : [])
                    ->filter(fn ($item) => is_string($item) && filter_var($item, FILTER_VALIDATE_URL) !== false)
                    ->values();

                return $localFiles->merge($existingUrls)->unique()->values()->all();
            });
    }

    /**
     * Resolve display URL for either local basename, relative disk path, or external URL.
     */
    public static function resolveUrl(?string $value, string $directory): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) || str_starts_with($value, 'data:')) {
            return $value;
        }

        $relative = str_contains($value, '/') ? ltrim($value, '/') : "{$directory}/{$value}";

        return Storage::disk('public')->url($relative);
    }

    /**
     * Shared ImageColumn supporting both legacy basenames and external image URLs.
     */
    public static function column(string $name, string $directory, string $label, int $size = 48): ImageColumn
    {
        return ImageColumn::make($name)
            ->label($label)
            ->imageSize($size)
            ->square()
            ->checkFileExistence(false)
            ->state(fn (?Model $record): ?string => self::resolveUrl($record?->{$name}, $directory));
    }

    /**
     * Shared ImageEntry for View/Infolist screens.
     */
    public static function entry(string $name, string $directory, string $label, int $size = 64): ImageEntry
    {
        return ImageEntry::make($name)
            ->label($label)
            ->imageSize($size)
            ->checkFileExistence(false)
            ->state(fn (?Model $record): ?string => self::resolveUrl($record?->{$name}, $directory));
    }
}
