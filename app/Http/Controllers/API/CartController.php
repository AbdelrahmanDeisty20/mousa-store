<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\createRequest;
use App\Models\{Cart, Offer, Product, Property};
use App\Service\CheckoutPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB};

class CartController extends Controller
{
    // عرض السلة النشطة للمستخدم
    public function index(Request $request, CheckoutPricingService $pricing)
    {
        $user = Auth::user();
        $now = now();
        $locale = app()->getLocale();

        $cart = Cart::with(['items' => function ($query) use ($now) {
            $query
                ->withTrashed()
                ->with([
                    'product',  // علاقة المنتج
                    'variantAttributes',
                    'offers' => function ($q) use ($now) {
                        $q
                            ->whereNotNull('discount_price')
                            ->where('discount_price', '>', 0)
                            ->where('start', '<=', $now)
                            ->where('end', '>=', $now);
                    }
                ]);
        }])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // تنظيف السلة تلقائياً: حذف المنتجات التي تم حذفها (Soft Delete) نهائياً من السلة عند التحديث
        if ($cart && $cart->items->isNotEmpty()) {
            $trashedItems = $cart->items->filter(fn($item) => $item->trashed());

            if ($trashedItems->isNotEmpty()) {
                $cart->items()->detach($trashedItems->pluck('id'));
                // تحديث القائمة في الذاكرة لتجاهل العناصر المحذوفة حتى لا تظهر في الاستجابة الحالية
                $cart->setRelation('items', $cart->items->diff($trashedItems));
            }
        }

        $setting = $pricing->resolveSetting(
            $request->filled('governorate_id') ? (int) $request->input('governorate_id') : null,
            $user->id
        );

        if (!$cart || $cart->items->isEmpty()) {
            $emptyTax = $pricing->calculateTax(0.0, $setting);

            return response()->json([
                'success' => true,
                'message' => 'No active cart with products found',
                'data' => [],
                'tax_rate' => $emptyTax['tax_rate'],
                'tax_amount' => 0.0,
            ]);
        }

        // ===== حساب الستوك لكل property =====
        $propertyIds = $cart->items->pluck('id');
        $stocks = DB::table('properties')
            ->select('id', 'stock')
            ->whereIn('id', $propertyIds)
            ->pluck('stock', 'id');

        $total = 0;

        // تحويل العناصر للشكل المطلوب يدوياً
        $transformedItems = $cart->items->map(function ($item) use ($stocks, $locale) {
            // السعر الأصلي
            $originalPrice = $item->price ?? 0;

            // حساب سعر العرض
            $unitPriceWithOffer = $originalPrice;
            $offer = $item->offers->first();

            if ($offer) {
                $discountPercent = $offer->discount_price ?? $offer->discount ?? 0;
                $unitPriceWithOffer = round($originalPrice * (1 - $discountPercent / 100), 2);
            }

            // تجهيز Attributes
            $attributes = $item->variantAttributes->mapWithKeys(function ($attr) use ($locale) {
                return [
                    ($locale === 'ar' ? $attr->key_ar : $attr->key_en) => ($locale === 'ar' ? $attr->value_ar : $attr->value_en)
                ];
            });

            return [
                'id' => $item->id,  // Property ID
                'product_id' => $item->product_id,
                'name' => $locale === 'ar' ? $item->product->name_ar : $item->product->name_en,
                'desc' => $locale === 'ar' ? $item->product->desc_ar : $item->product->desc_en,
                'stock' => (int) ($stocks[$item->id] ?? 0),
                'quantity' => $item->pivot->quantity,
                'line_total' => $unitPriceWithOffer * $item->pivot->quantity,
                'price' => (string) $originalPrice,
                'image_path' => $item->image_path,
                'attributes' => $attributes,
                'offers' => $item->offers->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'disscount_price' => $o->disscount_price
                    ];
                }),
            ];
        });

        // يمكن حساب المجموع هنا للتأكيد، أو الاعتماد على الحساب السابق
        $subtotal = round((float) $transformedItems->sum('line_total'), 2);
        $cart->total = $subtotal;
        $cart->save();

        $tax = $pricing->calculateTax($subtotal, $setting);

        return response()->json([
            'success' => true,
            'tax_rate' => $tax['tax_rate'],
            'tax_amount' => $tax['tax_amount'],
            'cart' => [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'total' => $cart->total,
                'subtotal' => $subtotal,
                'tax_rate' => $tax['tax_rate'],
                'tax_amount' => $tax['tax_amount'],
                'items' => $transformedItems,
            ],
        ]);
    }

    // إضافة أو تحديث منتج في السلة
    public function store(createRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        $property = Property::findOrFail($data['property_id']);

        // جلب الستوك الحالي للـ property
        $totalStock = $property->stock;

        if ($totalStock < $data['quantity']) {
            return response()->json([
                'success' => true,
                'message' => 'Not enough stock',
            ], 200);
        }

        $unitPrice = $property->price ?? 0;

        $offer = Offer::where('property_id', $property->id)
            ->where('start', '<=', Carbon::now())
            ->where('end', '>=', Carbon::now())
            ->first();

        if ($offer) {
            $discountPercent = $offer->discount_price ?? $offer->discount ?? 0;
            if ($discountPercent > 0) {
                $unitPrice = round($unitPrice * (1 - $discountPercent / 100), 2);
            }
        }

        $lineTotal = $unitPrice * $data['quantity'];

        DB::transaction(function () use ($user, $property, $data, $unitPrice, $lineTotal) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'status' => 'active'],
                ['total' => 0]
            );

            $existing = $cart
                ->items()
                ->where('property_id', $property->id)
                ->first();

            if ($existing) {
                $newQty = $existing->pivot->quantity + $data['quantity'];
                $cart->items()->updateExistingPivot($property->id, [
                    'quantity' => $newQty,
                    'unit_price' => $unitPrice,
                    'line_total' => $newQty * $unitPrice,
                ]);
            } else {
                $cart->items()->attach($property->id, [
                    'quantity' => $data['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $cart->load('items');
            $cart->total = $cart->items->sum(fn($i) => $i->pivot->line_total);
            $cart->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Property added to cart',
        ], 200);
    }

    // تحديث كمية المنتج

    public function update(Request $request, $propertyId)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user, $propertyId, $data) {
            $cart = Cart::where('user_id', $user->id)
                ->where('status', 'active')
                ->firstOrFail();

            // ===== property / variant =====
            $property = Property::withTrashed()->with('product')->findOrFail($propertyId);

            // موجود في السلة؟
            $cartItem = $cart
                ->items()
                ->where('properties.id', $propertyId)
                ->first();

            if (!$cartItem) {
                abort(404, 'Item not in cart');
            }

            // تحقق من الستوك
            if ($property->stock < $data['quantity']) {
                abort(422, 'Not enough stock');
            }

            // السعر من property
            $unitPrice = $property->price ?? 0;

            // العروض
            $offer = Offer::where('property_id', $property->id)
                ->where('start', '<=', now())
                ->where('end', '>=', now())
                ->first();

            if ($offer) {
                $discountPercent = $offer->discount_price ?? $offer->discount ?? 0;
                if ($discountPercent > 0) {
                    $unitPrice = round($unitPrice * (1 - $discountPercent / 100), 2);
                }
            }

            $lineTotal = $unitPrice * $data['quantity'];

            // تحديث pivot
            $cart
                ->items()
                ->updateExistingPivot($property->id, [
                    'quantity' => $data['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

            // تحديث إجمالي السلة
            $cart->load('items');
            $cart->total = $cart->items->sum(fn($i) => $i->pivot->line_total);
            $cart->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
        ]);
    }

    // حذف منتج من السلة
    public function destroy($propertyId)
    {
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        $cartItem = $cart
            ->items()
            ->withTrashed()  // Check trashed items too
            ->where('properties.id', $propertyId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => true,
                'message' => 'Item not found in cart',
            ]);
        }

        // حذف من السلة
        $cart
            ->items()
            ->detach($propertyId);

        // تحديث إجمالي السلة
        $cart->load('items');
        $cart->total = $cart->items->sum(fn($i) => $i->pivot->line_total);
        $cart->save();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }
}
