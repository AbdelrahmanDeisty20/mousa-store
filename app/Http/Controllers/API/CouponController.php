<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Service\CheckoutPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request, CheckoutPricingService $pricing): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
        ]);

        $user = $request->user() ?? auth('sanctum')->user();

        if ($request->filled('subtotal')) {
            $subtotal = (float) $validated['subtotal'];
        } else {
            $resolved = $pricing->resolveItemsAndSubtotal(
                $user?->id,
                $validated['items'] ?? null,
                validateStock: false
            );
            $subtotal = $resolved['subtotal'];
        }

        $result = $pricing->resolveCoupon($validated['code'], $subtotal);

        if ($result['error'] || ! $result['coupon']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? __('Invalid coupon code.'),
            ], 422);
        }

        /** @var Coupon $coupon */
        $coupon = $result['coupon'];
        $discountAmount = $result['discount_amount'];
        $finalTotal = max(0, round($subtotal - $discountAmount, 2));

        return response()->json([
            'success' => true,
            'message' => __('Coupon applied successfully'),
            'data' => [
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'discount_type' => $coupon->discount_type === 'percent' ? 'percent' : 'fixed',
                'discount_value' => (float) $coupon->discount_value,
                'subtotal' => round($subtotal, 2),
                'final_total' => $finalTotal,
            ],
        ]);
    }
}
