<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductBannerResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Filament\Support\MediaField;
use App\Models\ProductBanner;
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

class ProductBannerResource extends Resource
{
    protected static ?string $model = ProductBanner::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Marketing & CMS');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Product Ad Banners');
    }

    public static function getModelLabel(): string
    {
        return __('Product Ad Banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Product Ad Banners');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_product_banners');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_product_banners');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_product_banners');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_product_banners');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Advertisement Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('title_ar')
                        ->label(__('Title (Arabic)'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('title_en')
                        ->label(__('Title (English)'))
                        ->required()
                        ->maxLength(255),

                    Textarea::make('desc_ar')
                        ->label(__('Description (Arabic)'))
                        ->required()
                        ->rows(3),

                    Textarea::make('desc_en')
                        ->label(__('Description (English)'))
                        ->required()
                        ->rows(3),

                    TextInput::make('price')
                        ->label(__('Price'))
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->suffix(__('EGP'))
                        ->required(),

                    MediaField::singleUpload('image', 'advertisements', 2048)
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
                MediaField::column('image', 'advertisements', __('Image'), 64),

                TextColumn::make('title_ar')
                    ->label(__('Title (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title_en')
                    ->label(__('Title (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('EGP')
                    ->sortable(),
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
            'index' => Pages\ListProductBanners::route('/'),
            'create' => Pages\CreateProductBanner::route('/create'),
            'edit' => Pages\EditProductBanner::route('/{record}/edit'),
        ];
    }
}
