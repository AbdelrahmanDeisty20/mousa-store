<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Filament\Support\MediaField;
use App\Models\AttributeDefinition;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Products');
    }

    public static function getModelLabel(): string
    {
        return __('Product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_products');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_products');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_products');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_products');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'brand', 'variants.variantAttributes']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Product Information'))
                ->columns(2)
                ->schema([
                    TextInput::make('name_ar')
                        ->label(__('Name (Arabic)'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('name_en')
                        ->label(__('Name (English)'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('category_id')
                        ->label(__('Category'))
                        ->relationship('category', 'name_en')
                        ->getOptionLabelFromRecordUsing(fn (Category $record): string => trim("{$record->name_ar} / {$record->name_en}"))
                        ->searchable(['name_ar', 'name_en'])
                        ->preload()
                        ->required(),

                    Select::make('brand_id')
                        ->label(__('Brand'))
                        ->relationship('brand', 'name_en')
                        ->getOptionLabelFromRecordUsing(fn (Brand $record): string => trim("{$record->name_ar} / {$record->name_en}"))
                        ->searchable(['name_ar', 'name_en'])
                        ->preload()
                        ->required(),

                    Textarea::make('desc_ar')
                        ->label(__('Description (Arabic)'))
                        ->required()
                        ->rows(3),

                    Textarea::make('desc_en')
                        ->label(__('Description (English)'))
                        ->required()
                        ->rows(3),
                ]),

            Section::make(__('Variants & Pricing'))
                ->schema([
                    Repeater::make('variants')
                        ->label(__('Product Variants'))
                        ->relationship('variants')
                        ->minItems(1)
                        ->defaultItems(1)
                        ->collapsible()
                        ->addActionLabel(__('Add Variant'))
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step('0.01')
                                    ->suffix(__('EGP'))
                                    ->required(),

                                TextInput::make('stock')
                                    ->label(__('Stock'))
                                    ->integer()
                                    ->minValue(1)
                                    ->default(10)
                                    ->helperText(__('Stock must be at least 1'))
                                    ->required(),

                                MediaField::singleUpload('main_image', 'products', 2048)
                                    ->label(__('Main Image'))
                                    ->required(fn (?Model $record): bool => blank($record?->main_image)),

                                MediaField::multipleUpload('images', 'products', 2048)
                                    ->label(__('Gallery Images')),
                            ]),

                            Repeater::make('variantAttributes')
                                ->label(__('Attributes'))
                                ->relationship('variantAttributes')
                                ->columns(2)
                                ->defaultItems(1)
                                ->addActionLabel(__('Add Attribute'))
                                ->schema([
                                    Select::make('_preset_definition')
                                        ->label(__('Attribute Preset'))
                                        ->options(fn (): array => AttributeDefinition::query()
                                            ->get()
                                            ->mapWithKeys(fn (AttributeDefinition $def): array => [
                                                $def->id => trim("{$def->name_ar} / {$def->name_en}"),
                                            ])
                                            ->all())
                                        ->live()
                                        ->dehydrated(false)
                                        ->afterStateUpdated(function (?string $state, Set $set): void {
                                            if ($state && ($def = AttributeDefinition::find($state))) {
                                                $set('key_en', $def->name_en);
                                                $set('key_ar', $def->name_ar);
                                                $set('_preset_value', null);
                                            }
                                        }),

                                    Select::make('_preset_value')
                                        ->label(__('Value Preset'))
                                        ->options(function (Get $get): array {
                                            $defId = $get('_preset_definition');
                                            if (! $defId) {
                                                return [];
                                            }

                                            return AttributeValue::where('attribute_definition_id', $defId)
                                                ->get()
                                                ->mapWithKeys(fn (AttributeValue $val): array => [
                                                    $val->id => trim("{$val->value_ar} / {$val->value_en}"),
                                                ])
                                                ->all();
                                        })
                                        ->live()
                                        ->dehydrated(false)
                                        ->afterStateUpdated(function (?string $state, Set $set): void {
                                            if ($state && ($val = AttributeValue::find($state))) {
                                                $set('value_en', $val->value_en);
                                                $set('value_ar', $val->value_ar);
                                            }
                                        }),

                                    TextInput::make('key_ar')
                                        ->label(__('Key (Arabic)'))
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('key_en')
                                        ->label(__('Key (English)'))
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('value_ar')
                                        ->label(__('Value (Arabic)'))
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('value_en')
                                        ->label(__('Value (English)'))
                                        ->required()
                                        ->maxLength(255),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Product Information'))
                ->columns(2)
                ->schema([
                    TextEntry::make('name_ar')->label(__('Name (Arabic)')),
                    TextEntry::make('name_en')->label(__('Name (English)')),
                    TextEntry::make('category.name')->label(__('Category')),
                    TextEntry::make('brand.name')->label(__('Brand')),
                    TextEntry::make('desc_ar')->label(__('Description (Arabic)')),
                    TextEntry::make('desc_en')->label(__('Description (English)')),
                ]),

            Section::make(__('Variants'))
                ->schema([
                    RepeatableEntry::make('variants')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            ImageEntry::make('main_image')
                                ->label(__('Main Image'))
                                ->imageSize(64)
                                ->checkFileExistence(false)
                                ->state(fn ($record): ?string => MediaField::resolveUrl($record->main_image ?? null, 'products')),
                            TextEntry::make('price')->label(__('Price'))->money('EGP'),
                            TextEntry::make('stock')->label(__('Stock'))->badge(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label(__('Image'))
                    ->imageSize(48)
                    ->square()
                    ->checkFileExistence(false)
                    ->state(fn (Product $record): ?string => MediaField::resolveUrl(
                        $record->variants->first()?->main_image,
                        'products'
                    )),

                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable(['category_id']),

                TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->sortable(['brand_id']),

                TextColumn::make('variants_count')
                    ->label(__('Variants'))
                    ->counts('variants')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('price_range')
                    ->label(__('Price'))
                    ->state(function (Product $record): string {
                        $min = $record->variants->min('price');
                        $max = $record->variants->max('price');
                        if ($min === null) {
                            return '—';
                        }

                        return $min === $max
                            ? number_format((float) $min, 2) . ' ' . __('EGP')
                            : number_format((float) $min, 2) . ' - ' . number_format((float) $max, 2) . ' ' . __('EGP');
                    }),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name_en')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand_id')
                    ->label(__('Brand'))
                    ->relationship('brand', 'name_en')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
