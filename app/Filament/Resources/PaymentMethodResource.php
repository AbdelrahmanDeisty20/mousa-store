<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Settings & Support');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Payment Methods');
    }

    public static function getModelLabel(): string
    {
        return __('Payment Method');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payment Methods');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_settings');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_settings');
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Payment Method Settings'))
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label(__('Code'))
                        ->disabled(),

                    TextInput::make('sort_order')
                        ->label(__('Sort Order'))
                        ->numeric()
                        ->default(0)
                        ->required(),

                    TextInput::make('name_ar')
                        ->label(__('Name (Arabic)'))
                        ->maxLength(100)
                        ->required(),

                    TextInput::make('name_en')
                        ->label(__('Name (English)'))
                        ->maxLength(100)
                        ->required(),

                    TextInput::make('description_ar')
                        ->label(__('Description (Arabic)'))
                        ->maxLength(255),

                    TextInput::make('description_en')
                        ->label(__('Description (English)'))
                        ->maxLength(255),

                    TextInput::make('config.payment_url')
                        ->label(__('Gateway Redirect URL Template'))
                        ->helperText(__('Optional external URL (supports {order_id}, {payment_id}, {order_number}, {amount}).'))
                        ->url()
                        ->columnSpanFull(),

                    Textarea::make('config.instruction')
                        ->label(__('Payment Instructions'))
                        ->rows(2)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->searchable(),

                TextColumn::make('name_en')
                    ->label(__('Name (English)'))
                    ->searchable(),

                TextColumn::make('sort_order')
                    ->label(__('Sort Order'))
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
