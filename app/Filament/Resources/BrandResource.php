<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Filament\Support\MediaField;
use App\Models\Brand;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Brands');
    }

    public static function getModelLabel(): string
    {
        return __('Brand');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Brands');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_brands');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_brands');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_brands');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_brands');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Brand Details'))
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

                    MediaField::singleUpload('image', 'brands', 2048)
                        ->label(__('Logo'))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaField::column('image', 'brands', __('Logo')),

                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label(__('Products'))
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
