<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Support\AdminAccess;
use App\Models\Notification as UserNotification;
use App\Models\Order;
use App\Models\Property;
use App\Models\Setting;
use App\Service\FirebaseNotificationService;
use App\Service\ProductHelper;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification as FilamentNotification;
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
use Illuminate\Support\Facades\DB;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('Sales & Customers');
    }

    public static function getNavigationLabel(): string | Htmlable
    {
        return __('Orders');
    }

    public static function getModelLabel(): string
    {
        return __('Order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Orders');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        return AdminAccess::can('view_orders');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return AdminAccess::can('delete_orders', false);
    }

    public static function canDeleteAny(): bool
    {
        return AdminAccess::can('delete_orders', false);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user',
            'governorate',
            'items.product',
            'items.variantAttributes',
        ]);
    }

    public static function makeProcessAction(): Action
    {
        return Action::make('process')
            ->label(__('Process Order'))
            ->icon(Heroicon::OutlinedTruck)
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'paid'], true) && AdminAccess::can('edit_orders'))
            ->action(function (Order $record): void {
                $record->update(['status' => 'processing']);

                if ($user = $record->user) {
                    $title = 'تحديث حالة الطلب';
                    $body = "تم شحن الطلب رقم {$record->order_number} وسيصل إليك قريبًا";
                    self::notifyCustomer($user->id, $record->id, 'processing', $title, $body);
                }

                FilamentNotification::make()
                    ->title(__('Order moved to processing'))
                    ->success()
                    ->send();
            });
    }

    public static function makeCompleteAction(): Action
    {
        return Action::make('complete')
            ->label(__('Complete Order'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'processing', 'paid'], true) && AdminAccess::can('edit_orders'))
            ->action(function (Order $record): void {
                $record->update(['status' => 'completed']);

                FilamentNotification::make()
                    ->title(__('Order marked as completed'))
                    ->success()
                    ->send();
            });
    }

    public static function makeCancelAction(): Action
    {
        return Action::make('cancel')
            ->label(__('Cancel Order'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->schema([
                Textarea::make('notice')
                    ->label(__('Cancellation Reason / Notice'))
                    ->rows(3),
            ])
            ->visible(fn (Order $record): bool => ! in_array($record->status, ['cancelled', 'completed'], true) && AdminAccess::can('edit_orders'))
            ->action(function (Order $record, array $data): void {
                if ($record->status === 'cancelled') {
                    return;
                }

                DB::transaction(function () use ($record, $data): void {
                    $record->update([
                        'status' => 'cancelled',
                        'notice' => $data['notice'] ?? null,
                    ]);

                    foreach ($record->items as $item) {
                        $item->stock += (int) ($item->pivot->quantity ?? 0);
                        $item->save();

                        if (method_exists($item, 'trashed') && $item->trashed()) {
                            $item->restore();
                        }
                    }
                });

                if ($user = $record->user) {
                    $title = 'تحديث حالة الطلب';
                    $body = "تم إلغاء الطلب رقم {$record->order_number} ";
                    self::notifyCustomer($user->id, $record->id, 'cancelled', $title, $body);
                }

                FilamentNotification::make()
                    ->title(__('Order cancelled successfully'))
                    ->warning()
                    ->send();
            });
    }

    private static function notifyCustomer(int $userId, int $orderId, string $status, string $title, string $body): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'type' => 'order_status',
            'data' => ['order_id' => $orderId, 'status' => $status],
        ]);

        $order = Order::with('user.fcmTokens')->find($orderId);
        $tokens = $order?->user?->fcmTokens?->pluck('fcm_token') ?? collect();

        if ($tokens->isEmpty()) {
            return;
        }

        $service = app(FirebaseNotificationService::class);
        foreach ($tokens as $token) {
            try {
                $service->sendToToken($token, $title, $body, [
                    'type' => 'order_status',
                    'order_id' => $orderId,
                    'status' => $status,
                ]);
            } catch (\Throwable) {
                // Keep workflow resilient if an FCM token is expired
            }
        }
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Order Summary'))
                ->columns(3)
                ->schema([
                    TextEntry::make('order_number')->label(__('Order Number')),
                    TextEntry::make('status')
                        ->label(__('Status'))
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'processing' => 'info',
                            'paid', 'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('created_at')->label(__('Created At'))->dateTime('Y-m-d H:i'),
                    TextEntry::make('user_name')->label(__('Customer Name')),
                    TextEntry::make('user_phone')->label(__('Phone')),
                    TextEntry::make('governorate.name')->label(__('Governorate')),
                    TextEntry::make('user_address')->label(__('Address'))->columnSpan(2),
                    TextEntry::make('shipping_cost')
                        ->label(__('Shipping Cost'))
                        ->state(function (Order $record): string {
                            $cost = Setting::where('governorate_id', $record->governorate_id)->value('shipping') ?? 0;

                            return number_format((float) $cost, 2) . ' ' . __('EGP');
                        }),
                    TextEntry::make('total_price')->label(__('Total Price'))->money('EGP'),
                    TextEntry::make('notice')
                        ->label(__('Notice'))
                        ->visible(fn (Order $record): bool => filled($record->notice))
                        ->columnSpanFull(),
                ]),

            Section::make(__('Order Items'))
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('product.name')
                                ->label(__('Product'))
                                ->state(function (Property $record): string {
                                    $name = $record->product?->name ?: $record->product?->name_ar ?: "#{$record->product_id}";
                                    $attrs = ProductHelper::getAttributesString($record, app()->getLocale());

                                    return $attrs !== '' ? "{$name} ({$attrs})" : $name;
                                }),
                            TextEntry::make('pivot.quantity')->label(__('Quantity')),
                            TextEntry::make('pivot.price')->label(__('Unit Price'))->money('EGP'),
                            TextEntry::make('line_total')
                                ->label(__('Subtotal'))
                                ->state(fn (Property $record): string => number_format(
                                    (float) ($record->pivot->quantity ?? 0) * (float) ($record->pivot->price ?? 0),
                                    2
                                ) . ' ' . __('EGP')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('Order Number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label(__('Customer'))
                    ->searchable(['user_name', 'user_phone']),

                TextColumn::make('user_phone')
                    ->label(__('Phone'))
                    ->searchable(),

                TextColumn::make('governorate.name')
                    ->label(__('Governorate')),

                TextColumn::make('total_price')
                    ->label(__('Total Price'))
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'paid', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __(ucfirst($state)))
                    ->sortable(),

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
                        'processing' => __('Processing'),
                        'paid' => __('Paid'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                self::makeProcessAction(),
                self::makeCompleteAction(),
                self::makeCancelAction(),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
