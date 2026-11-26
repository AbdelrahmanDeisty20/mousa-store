<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminNotificationResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Notification as UserNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class AdminNotificationResource extends Resource
{
    protected static ?string $model = UserNotification::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?string $slug = 'notifications';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Settings & Support');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Notifications');
    }

    public static function getModelLabel(): string
    {
        return __('Notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Notifications');
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        $count = UserNotification::where('user_id', $userId)->where('is_read', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::user() !== null;
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
        return parent::getEloquentQuery()
            ->when(Auth::id(), fn (Builder $query, int $userId) => $query->where('user_id', $userId));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('body')
                    ->label(__('Message'))
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'order', 'order_status' => 'primary',
                        'low_stock', 'low_stock_admin', 'out_of_stock_admin' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('is_read')
                    ->label(__('Read'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label(__('Read Status')),

                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'order' => __('Orders'),
                        'low_stock_admin' => __('Low Stock'),
                        'out_of_stock_admin' => __('Out of Stock'),
                        'payment_completed' => __('Completed'),
                        'payment_failed' => __('Failed'),
                    ]),
            ])
            ->recordActions([
                Action::make('markRead')
                    ->label(__('Mark as Read'))
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (UserNotification $record): bool => ! $record->is_read)
                    ->action(function (UserNotification $record): void {
                        $record->markAsRead();
                        FilamentNotification::make()->title(__('Notification marked as read'))->success()->send();
                    }),

                Action::make('openOrder')
                    ->label(__('View Order'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn (UserNotification $record): bool => filled($record->data['order_id'] ?? null))
                    ->url(fn (UserNotification $record): string => OrderResource::getUrl('view', ['record' => $record->data['order_id']])),

                Action::make('openProduct')
                    ->label(__('Edit Product'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->visible(fn (UserNotification $record): bool => filled($record->data['product_id'] ?? null) && blank($record->data['order_id'] ?? null))
                    ->url(fn (UserNotification $record): string => ProductResource::getUrl('edit', ['record' => $record->data['product_id']])),

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
            'index' => Pages\ListAdminNotifications::route('/'),
        ];
    }
}
