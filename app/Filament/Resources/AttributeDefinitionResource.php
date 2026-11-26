<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeDefinitionResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\AttributeDefinition;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AttributeDefinitionResource extends Resource
{
    protected static ?string $model = AttributeDefinition::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Attributes');
    }

    public static function getModelLabel(): string
    {
        return __('Attribute');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Attributes');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_attributes');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_attributes');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_attributes');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_attributes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Attribute Definition'))
                ->columns(2)
                ->schema([
                    TextInput::make('name_ar')
                        ->label(__('Name (Arabic)'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name_en')
                        ->label(__('Name (English)'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                ]),

            Section::make(__('Predefined Values'))
                ->schema([
                    Repeater::make('values')
                        ->label(__('Values'))
                        ->relationship('values')
                        ->columns(2)
                        ->defaultItems(1)
                        ->addActionLabel(__('Add Value'))
                        ->schema([
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('values_count')
                    ->label(__('Values Count'))
                    ->counts('values')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => Pages\ListAttributeDefinitions::route('/'),
            'create' => Pages\CreateAttributeDefinition::route('/create'),
            'edit' => Pages\EditAttributeDefinition::route('/{record}/edit'),
        ];
    }
}
