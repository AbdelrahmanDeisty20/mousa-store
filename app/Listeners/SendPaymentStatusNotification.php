<?php

namespace App\Listeners;

use App\Events\PaymentStatusEvent;
use App\Filament\Resources\OrderResource;
use App\Service\AdminNotificationDispatcher;
use Filament\Support\Icons\Heroicon;

class SendPaymentStatusNotification
{
    /**
     * Handle the payment status notification across legacy notifications and Filament bell alerts.
     */
    public function handle(PaymentStatusEvent $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;
        $user = $order?->user;

        if (! $order) {
            return;
        }

        if (! in_array($payment->status, ['completed', 'failed'], true)) {
            return;
        }

        $customerName = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: ($user?->email ?? 'Customer');
        $isCompleted = $payment->status === 'completed';

        $title = $isCompleted
            ? 'تم الدفع بنجاح / Payment Completed'
            : 'فشل عملية الدفع / Payment Failed';

        $body = $isCompleted
            ? "تم استلام دفعة بقيمة {$payment->amount} من {$customerName} للطلب رقم {$order->order_number}"
            : "فشلت عملية دفع بقيمة {$payment->amount} من {$customerName} للطلب رقم {$order->order_number}";

        $url = rescue(
            fn (): string => OrderResource::getUrl('view', ['record' => $order], panel: 'admin'),
            report: false
        );

        AdminNotificationDispatcher::notifyAdmins(
            title: $title,
            body: $body,
            type: $isCompleted ? 'payment_completed' : 'payment_failed',
            data: ['order_id' => $order->id, 'payment_id' => $payment->id],
            actionLabel: __('View Order'),
            actionUrl: $url,
            status: $isCompleted ? 'success' : 'danger',
            icon: Heroicon::OutlinedCreditCard,
        );
    }
}
