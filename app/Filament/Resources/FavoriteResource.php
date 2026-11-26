<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FavoriteResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Favorite;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Sales & Customers');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Favorites');
    }

    public static function getModelLabel(): string
    {
        return __('Favorite');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Favorites');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_favorites');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'product']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.email')
                    ->label(__('Customer'))
                    ->formatStateUsing(fn (Favorite $record): string => $record->user?->getFilamentName() ?? '—'),

                TextColumn::make('product.name_ar')
                    ->label(__('Product'))
                    ->formatStateUsing(fn (Favorite $record): string => $record->product?->name ?? '—')
                    ->searchable(['product.name_ar', 'product.name_en']),

                IconColumn::make('is_favorite')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFavorites::route('/'),
        ];
    }
}
