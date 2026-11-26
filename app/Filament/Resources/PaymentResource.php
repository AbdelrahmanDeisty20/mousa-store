<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Sales & Customers');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Payments');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payments');
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_payments');
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
        return parent::getEloquentQuery()->with(['order']);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Payment Details'))
                ->columns(2)
                ->schema([
                    TextEntry::make('order.order_number')->label(__('Order Number')),
                    TextEntry::make('method')->label(__('Payment Method'))->badge(),
                    TextEntry::make('amount')->label(__('Amount'))->money('EGP'),
                    TextEntry::make('status')->label(__('Status'))->badge(),
                    TextEntry::make('transaction_id')->label(__('Transaction ID')),
                    TextEntry::make('created_at')->label(__('Created At'))->dateTime('Y-m-d H:i'),
                    TextEntry::make('response')->label(__('Gateway Response'))->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label(__('Order Number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('method')
                    ->label(__('Payment Method'))
                    ->badge(),

                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('transaction_id')
                    ->label(__('Transaction ID'))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'completed' => __('Completed'),
                        'failed' => __('Failed'),
                    ]),
                SelectFilter::make('method')
                    ->label(__('Payment Method'))
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'vodafone_cash' => 'Vodafone Cash',
                        'fawry' => 'Fawry',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
