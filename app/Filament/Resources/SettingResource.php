<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Governorate;
use App\Models\Setting;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Settings & Support');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Shipping Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Shipping Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Shipping Settings');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_settings');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_settings');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_settings');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_settings');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['governorate']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Governorate Shipping Rate'))
                ->columns(2)
                ->schema([
                    Select::make('governorate_id')
                        ->label(__('Governorate'))
                        ->relationship('governorate', 'name_en')
                        ->getOptionLabelFromRecordUsing(fn (Governorate $record): string => trim("{$record->name_ar} / {$record->name_en}"))
                        ->searchable(['name_ar', 'name_en'])
                        ->preload()
                        ->unique(ignoreRecord: true)
                        ->required(),

                    TextInput::make('shipping')
                        ->label(__('Shipping Fee'))
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->suffix(__('EGP'))
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'asc')
            ->columns([
                TextColumn::make('governorate.name_ar')
                    ->label(__('Governorate (Arabic)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('governorate.name_en')
                    ->label(__('Governorate (English)'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shipping')
                    ->label(__('Shipping Fee'))
                    ->money('EGP')
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
