<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Sales & Customers');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Coupons');
    }

    public static function getModelLabel(): string
    {
        return __('Coupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Coupons');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_offers');
    }

    public static function canCreate(): bool
    {
        return AdminAccess::can('create_offers');
    }

    public static function canEdit(Model $record): bool
    {
        return AdminAccess::can('edit_offers');
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_offers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Coupon Details'))
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label(__('Promo Code'))
                        ->placeholder('MOUSA10')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->required(),

                    Select::make('discount_type')
                        ->label(__('Discount Type'))
                        ->options([
                            'fixed' => __('Fixed Amount'),
                            'percent' => __('Percentage (%)'),
                        ])
                        ->default('fixed')
                        ->required(),

                    TextInput::make('discount_value')
                        ->label(__('Discount Value'))
                        ->numeric()
                        ->minValue(0.01)
                        ->step('0.01')
                        ->required(),

                    TextInput::make('min_order_amount')
                        ->label(__('Minimum Order Amount'))
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->suffix(__('EGP')),

                    TextInput::make('max_discount_amount')
                        ->label(__('Max Discount Amount'))
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->suffix(__('EGP')),

                    TextInput::make('usage_limit')
                        ->label(__('Usage Limit'))
                        ->numeric()
                        ->minValue(1)
                        ->step('1'),

                    DateTimePicker::make('starts_at')
                        ->label(__('Start Date'))
                        ->native(false),

                    DateTimePicker::make('expires_at')
                        ->label(__('End Date'))
                        ->afterOrEqual('starts_at')
                        ->native(false),

                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label(__('Promo Code'))
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('discount_type')
                    ->label(__('Discount Type'))
                    ->formatStateUsing(fn (string $state): string => $state === 'percent' ? __('Percentage (%)') : __('Fixed Amount'))
                    ->sortable(),

                TextColumn::make('discount_value')
                    ->label(__('Discount Value'))
                    ->formatStateUsing(fn (Coupon $record): string => $record->discount_type === 'percent'
                        ? rtrim(rtrim(number_format((float) $record->discount_value, 2), '0'), '.') . '%'
                        : number_format((float) $record->discount_value, 2) . ' ' . __('EGP'))
                    ->sortable(),

                TextColumn::make('used_count')
                    ->label(__('Usage'))
                    ->formatStateUsing(fn (Coupon $record): string => $record->usage_limit
                        ? "{$record->used_count} / {$record->usage_limit}"
                        : (string) $record->used_count)
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('Active')),

                TextColumn::make('expires_at')
                    ->label(__('End Date'))
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—')
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
