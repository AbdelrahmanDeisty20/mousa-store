<?php

namespace App\Listeners;

use App\Events\PaymentStatusEvent;
use App\Models\{Notification, User};

class SendPaymentStatusNotification
{
    /**
     * Handle the payment status notification using the unified notifications schema.
     */
    public function handle(PaymentStatusEvent $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;
        $user = $order?->user;

        if (! $order) {
            return;
        }

        $customerName = trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: ($user?->email ?? 'Customer');
        $admins = User::where('user_type', 'admin')->get();

        foreach ($admins as $admin) {
            if ($payment->status === 'completed') {
                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'تم الدفع بنجاح / Payment Completed',
                    'body'    => "تم استلام دفعة بقيمة {$payment->amount} من {$customerName} للطلب رقم {$order->order_number}",
                    'type'    => 'payment_completed',
                    'data'    => ['order_id' => $order->id, 'payment_id' => $payment->id],
                    'is_read' => false,
                ]);
            } elseif ($payment->status === 'failed') {
                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'فشل عملية الدفع / Payment Failed',
                    'body'    => "فشلت عملية دفع بقيمة {$payment->amount} من {$customerName} للطلب رقم {$order->order_number}",
                    'type'    => 'payment_failed',
                    'data'    => ['order_id' => $order->id, 'payment_id' => $payment->id],
                    'is_read' => false,
                ]);
            }
        }
    }
}
