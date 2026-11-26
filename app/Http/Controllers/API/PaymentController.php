<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\PaymentStatusEvent;
use App\Models\{Order, Payment, PaymentMethod};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function createPayment(Request $request, $orderId)
    {
        $user = $request->user() ?? auth('sanctum')->user();

        $orderQuery = Order::where('id', $orderId);
        $user
            ? $orderQuery->where('user_id', $user->id)
            : $orderQuery->whereNull('user_id');

        $order = $orderQuery->firstOrFail();

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return response()->json(['success' => false, 'message' => 'Order not payable', 'data' => []], 200);
        }

        $activeMethods = PaymentMethod::activeCodes();
        $data = $request->validate([
            'method' => ['required', 'string', Rule::in($activeMethods)],
        ]);

        $methodCode = $data['method'];
        $paymentMethod = PaymentMethod::where('code', $methodCode)->first();

        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => $methodCode,
            'amount' => $order->total_price,
            'status' => 'pending',
        ]);

        $paymentUrl = $this->resolveConfiguredPaymentUrl($paymentMethod, $payment, $order);
        $referenceCode = null;
        $providerResponse = [];

        if ($methodCode === 'cash') {
            $order->update(['status' => 'paid']);
            $providerResponse = [
                'instruction' => 'Pay with cash upon delivery.',
                'note' => 'No online payment needed. Your order will be processed.',
            ];
            $payment->update(['response' => json_encode($providerResponse), 'status' => 'completed']);
            event(new PaymentStatusEvent($payment));
        } elseif ($methodCode === 'fawry') {
            $referenceCode = 'FW-' . strtoupper(Str::random(8));
            $providerResponse = [
                'invoice_id' => $referenceCode,
                'reference_code' => $referenceCode,
                'payment_url' => $paymentUrl,
            ];
            $payment->update(['response' => json_encode($providerResponse)]);
        } elseif ($methodCode === 'vodafone_cash') {
            $referenceCode = 'VC-' . strtoupper(Str::random(8));
            $providerResponse = [
                'reference' => $referenceCode,
                'reference_code' => $referenceCode,
                'payment_url' => $paymentUrl,
                'instruction' => $paymentMethod?->description ?: 'Send payment via Vodafone Cash using the reference code.',
            ];
            $payment->update(['response' => json_encode($providerResponse)]);
        } else {
            // credit_card, valu, sympl, or additional dashboard-enabled gateways
            $referenceCode = strtoupper($methodCode) . '-' . strtoupper(Str::random(8));
            $providerResponse = [
                'reference_code' => $referenceCode,
                'payment_url' => $paymentUrl,
                'instruction' => $paymentMethod?->description ?: __('Complete payment using the provided reference or checkout link.'),
            ];
            $payment->update(['response' => json_encode($providerResponse)]);
        }

        return response()->json([
            'success' => true,
            'payment' => $payment->fresh(),
            'provider' => $providerResponse,
            'payment_url' => $paymentUrl,
            'reference_code' => $referenceCode,
        ]);
    }

    private function resolveConfiguredPaymentUrl(?PaymentMethod $paymentMethod, Payment $payment, Order $order): ?string
    {
        $template = trim((string) ($paymentMethod?->config['payment_url'] ?? ''));
        if ($template === '') {
            return null;
        }

        return strtr($template, [
            '{payment_id}' => (string) $payment->id,
            '{order_id}' => (string) $order->id,
            '{order_number}' => (string) $order->order_number,
            '{amount}' => (string) $payment->amount,
        ]);
    }

    // public callback endpoints — Fawry
    public function fawryCallback(Request $request)
    {
        $payload = $request->all();
        // TODO: verify signature HMAC as per Fawry docs (implement real check)
        $invoiceId = $payload['invoice_id'] ?? null;
        $status = $payload['status'] ?? null;

        // find payment by invoice id in response
        $payment = Payment::where('response', 'like', "%{$invoiceId}%")->first();
        if (!$payment) return response()->json(['message'=>'Payment not found'], 404);

        DB::transaction(function() use ($payment, $status, $payload) {
            $payment->response = json_encode($payload);
            if (in_array(strtolower($status), ['paid','success','completed'])) {
                $payment->status = 'completed';
                $payment->transaction_id = $payload['transaction_id'] ?? null;
                $payment->save();

                $order = $payment->order;
                $order->status = 'paid';
                $order->save();
            } else {
                $payment->status = 'failed';
                $payment->save();

                $payment->order->update(['status' => 'cancelled']);
            }
        });

        return response()->json(['message' => 'ok']);
    }

    // public callback endpoint — Vodafone Cash
    public function vodafoneCallback(Request $request)
    {
        $payload = $request->all();
        // TODO: verify signature per Vodafone docs
        $reference = $payload['reference'] ?? null;
        $status = $payload['status'] ?? null;

        $payment = Payment::where('response', 'like', "%{$reference}%")->first();
        if (!$payment) return response()->json(['message'=>'Payment not found'], 404);

        DB::transaction(function() use ($payment, $status, $payload) {
            $payment->response = json_encode($payload);
            if (in_array(strtolower($status), ['paid','success','completed'])) {
                $payment->status = 'completed';
                $payment->transaction_id = $payload['transaction_id'] ?? null;
                $payment->save();

                $order = $payment->order;
                $order->status = 'paid';
                $order->save();
            } else {
                $payment->status = 'failed';
                $payment->save();

                $payment->order->update(['status' => 'cancelled']);
            }
        });

        return response()->json(['message' => 'ok']);
    }
}