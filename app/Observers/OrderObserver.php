<?php

namespace App\Observers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Service\AdminNotificationDispatcher;
use Filament\Support\Icons\Heroicon;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->status !== 'pending') {
            return;
        }

        $title = 'طلب جديد / New Order';
        $body = "تم استلام طلب جديد رقم ({$order->order_number}) بقيمة {$order->total_price} ج.م | New order received: {$order->order_number}";

        $url = rescue(
            fn (): string => OrderResource::getUrl('view', ['record' => $order], panel: 'admin'),
            report: false
        );

        AdminNotificationDispatcher::notifyAdmins(
            title: $title,
            body: $body,
            type: 'order',
            data: ['order_id' => $order->id],
            actionLabel: __('View Order'),
            actionUrl: $url,
            status: 'success',
            icon: Heroicon::OutlinedShoppingBag,
        );
    }
}
