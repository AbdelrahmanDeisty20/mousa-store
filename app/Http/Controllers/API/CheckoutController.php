<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CheckoutRequest;
use App\Models\{Coupon, Order, Property};
use App\Service\CheckoutPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function checkout(CheckoutRequest $request, CheckoutPricingService $pricing)
    {
        $data = $request->validated();
        $user = $request->user() ?? auth('sanctum')->user();

        $resolved = $pricing->resolveItemsAndSubtotal(
            $user?->id,
            $data['items'] ?? null,
            validateStock: true
        );

        if ($resolved['error'] !== null) {
            if ($resolved['error'] === 'Cart is empty') {
                return response()->json([
                    'message' => 'Cart is empty',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => $resolved['error'],
            ], 200);
        }

        $productsToAdd = $resolved['items'];
        $subtotal = $resolved['subtotal'];
        $cart = $resolved['cart'];

        if ($productsToAdd === []) {
            return response()->json([
                'message' => 'No products available in cart.',
            ], 200);
        }

        $couponResult = $pricing->resolveCoupon($data['coupon_code'] ?? null, $subtotal);
        if ($couponResult['error'] !== null) {
            return response()->json([
                'success' => false,
                'message' => $couponResult['error'],
            ], 422);
        }

        $settings = $pricing->resolveSetting((int) $data['governorate_id'], $user?->id);
        $shipping = (float) ($settings?->shipping ?? 0);
        $estimatedDays = $settings?->estimated_days ?: '2-5';

        $coupon = $couponResult['coupon'];
        $discountAmount = $couponResult['discount_amount'];
        $taxableSubtotal = max(0, round($subtotal - $discountAmount, 2));
        $tax = $pricing->calculateTax($taxableSubtotal, $settings);
        $taxAmount = $tax['tax_amount'];
        $taxRate = $tax['tax_rate'];
        $total = round($taxableSubtotal + $taxAmount + $shipping, 2);

        DB::beginTransaction();

        try {
            $appliedCoupon = null;
            if ($coupon) {
                $appliedCoupon = Coupon::whereKey($coupon->id)->lockForUpdate()->first();
                if (! $appliedCoupon || ($limitError = $appliedCoupon->validateForSubtotal($subtotal))) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => $limitError ?? __('Invalid coupon code.'),
                    ], 422);
                }

                $appliedCoupon->increment('used_count');
            }

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'user_id' => $user?->id,
                'user_name' => $data['user_name'],
                'user_phone' => $data['user_phone'],
                'user_email' => $data['email'] ?? $data['user_email'] ?? $user?->email,
                'user_address' => $data['user_address'],
                'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
                'subtotal' => $subtotal,
                'coupon_id' => $appliedCoupon?->id,
                'coupon_code' => $appliedCoupon?->code,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shipping,
                'total_price' => $total,
                'status' => 'pending',
                'governorate_id' => $data['governorate_id'],
            ]);

            foreach ($productsToAdd as $product) {
                $order->items()->syncWithoutDetaching([
                    $product['id'] => [
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                    ],
                ]);

                Property::withTrashed()->find($product['id'])?->decrement('stock', $product['quantity']);
            }

            if ($cart) {
                DB::table('cart_items')
                    ->where('cart_id', $cart->id)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'coupon_code' => $appliedCoupon?->code,
                'shipping' => $shipping,
                'estimated_days' => $estimatedDays,
                'order' => $order->load('items'),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 200);
        }
    }
}
