<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Filament\Support\MediaField;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Marketing & CMS');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Banners');
    }

    public static function getModelLabel(): string
    {
        return __('Banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Banners');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_banners');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_banners');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_banners');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_banners');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Banner Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('title_ar')
                        ->label(__('Title (Arabic)'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('title_en')
                        ->label(__('Title (English)'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Textarea::make('short_desc_ar')
                        ->label(__('Short Description (Arabic)'))
                        ->required()
                        ->rows(3),

                    Textarea::make('short_desc_en')
                        ->label(__('Short Description (English)'))
                        ->required()
                        ->rows(3),

                    MediaField::singleUpload('image', 'banners', 4096)
                        ->label(__('Banner Image'))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaField::column('image', 'banners', __('Image'), 64),

                TextColumn::make('title_ar')
                    ->label(__('Title (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title_en')
                    ->label(__('Title (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('short_desc_ar')
                    ->label(__('Description'))
                    ->limit(45),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->after(fn () => Cache::flush()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->after(fn () => Cache::flush()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
