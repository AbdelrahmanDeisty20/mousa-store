<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Filament\Support\MediaField;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Categories');
    }

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categories');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_categories');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_categories');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_categories');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Category Details'))
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

                    Select::make('parent_id')
                        ->label(__('Parent Category'))
                        ->options(fn (?Category $record): array => Category::query()
                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                            ->get()
                            ->mapWithKeys(fn (Category $cat): array => [
                                $cat->id => trim("{$cat->name_ar} / {$cat->name_en}"),
                            ])
                            ->all())
                        ->searchable()
                        ->nullable()
                        ->placeholder(__('Main Category'))
                        ->formatStateUsing(fn ($state) => ($state === 0 || $state === '0') ? null : $state)
                        ->dehydrateStateUsing(fn ($state) => $state ?: null),

                    TextInput::make('sort_order')
                        ->label(__('Sort Order'))
                        ->numeric()
                        ->default(0)
                        ->required(),

                    MediaField::singleUpload('image', 'categories', 2048)
                        ->label(__('Image'))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->afterReordering(fn () => Cache::flush())
            ->columns([
                MediaField::column('image', 'categories', __('Image')),

                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('parent.name_en')
                    ->label(__('Parent Category'))
                    ->formatStateUsing(fn (Category $record): string => $record->parent ? ($record->parent->name ?: $record->parent->name_en) : __('Main Category'))
                    ->placeholder(__('Main Category')),

                TextColumn::make('products_count')
                    ->label(__('Products'))
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
