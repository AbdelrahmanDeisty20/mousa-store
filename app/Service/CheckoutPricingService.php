<?php

namespace App\Service;

use App\Models\{Address, Cart, Coupon, Property, Setting};

class CheckoutPricingService
{
    /**
     * Resolve effective shipping and tax settings for a governorate, falling back to user address or global default.
     */
    public function resolveSetting(?int $governorateId = null, ?int $userId = null): ?Setting
    {
        if ($governorateId) {
            $setting = Setting::where('governorate_id', $governorateId)->first();
            if ($setting) {
                return $setting;
            }
        }

        if ($userId) {
            $addressGovId = Address::where('user_id', $userId)->value('governorate_id');
            if ($addressGovId && ($setting = Setting::where('governorate_id', $addressGovId)->first())) {
                return $setting;
            }
        }

        return Setting::first();
    }

    /**
     * Compute tax rate (%) and tax amount for a taxable subtotal.
     *
     * @return array{tax_rate: float, tax_amount: float}
     */
    public function calculateTax(float $amount, ?Setting $setting): array
    {
        $taxRate = (float) ($setting?->tax ?? 0);
        $taxAmount = $amount > 0 && $taxRate > 0
            ? round(($taxRate / 100) * $amount, 2)
            : 0.0;

        return [
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Compute active-offer-adjusted unit price for a product variant.
     */
    public function discountedUnitPrice(Property $property): float
    {
        $originalPrice = (float) ($property->price ?? 0);
        $now = now();

        $offer = $property->offers
            ->first(fn ($item): bool => $item->start <= $now && $item->end >= $now);

        if (! $offer) {
            return $originalPrice;
        }

        $discountPercent = (float) ($offer->discount_price ?? $offer->discount ?? 0);
        if ($discountPercent <= 0) {
            return $originalPrice;
        }

        return round($originalPrice * (1 - $discountPercent / 100), 2);
    }

    /**
     * Resolve line items and subtotal either from a raw items payload (guest checkout / coupon preview)
     * or from an authenticated user's active DB cart.
     *
     * @param  array<int, array<string, mixed>>|null  $rawItems
     * @return array{items: array<int, array{id: int, quantity: int, price: float}>, subtotal: float, cart: ?Cart, error: ?string}
     */
    public function resolveItemsAndSubtotal(?int $userId = null, ?array $rawItems = null, bool $validateStock = true): array
    {
        if (is_array($rawItems) && $rawItems !== []) {
            $normalized = collect($rawItems)
                ->map(fn (array $row): array => [
                    'property_id' => (int) ($row['property_id'] ?? $row['id'] ?? 0),
                    'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
                ])
                ->filter(fn (array $row): bool => $row['property_id'] > 0)
                ->values();

            $properties = Property::withTrashed()
                ->with(['offers', 'product', 'variantAttributes'])
                ->whereIn('id', $normalized->pluck('property_id'))
                ->get()
                ->keyBy('id');

            $items = [];
            $subtotal = 0.0;

            foreach ($normalized as $entry) {
                $property = $properties->get($entry['property_id']);
                if (! $property) {
                    return ['items' => [], 'subtotal' => 0.0, 'cart' => null, 'error' => __('Selected product variant was not found.')];
                }

                if ($validateStock && ($property->stock <= 0 || $property->trashed() || $property->stock < $entry['quantity'])) {
                    return [
                        'items' => [],
                        'subtotal' => 0.0,
                        'cart' => null,
                        'error' => $this->outOfStockMessage($property),
                    ];
                }

                $price = $this->discountedUnitPrice($property);
                $items[] = [
                    'id' => $property->id,
                    'quantity' => $entry['quantity'],
                    'price' => $price,
                ];
                $subtotal += $price * $entry['quantity'];
            }

            return [
                'items' => $items,
                'subtotal' => round($subtotal, 2),
                'cart' => null,
                'error' => null,
            ];
        }

        if (! $userId) {
            return ['items' => [], 'subtotal' => 0.0, 'cart' => null, 'error' => 'Cart is empty'];
        }

        $cart = Cart::with(['items' => function ($q): void {
            $q->withTrashed()->with(['offers', 'product', 'variantAttributes']);
        }])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return ['items' => [], 'subtotal' => 0.0, 'cart' => $cart, 'error' => 'Cart is empty'];
        }

        $items = [];
        $subtotal = 0.0;

        foreach ($cart->items as $item) {
            if ($validateStock && ($item->stock <= 0 || $item->trashed() || $item->stock < $item->pivot->quantity)) {
                return [
                    'items' => [],
                    'subtotal' => 0.0,
                    'cart' => $cart,
                    'error' => $this->outOfStockMessage($item),
                ];
            }

            $price = $this->discountedUnitPrice($item);
            $qty = (int) $item->pivot->quantity;
            $items[] = [
                'id' => $item->id,
                'quantity' => $qty,
                'price' => $price,
            ];
            $subtotal += $price * $qty;
        }

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'cart' => $cart,
            'error' => null,
        ];
    }

    /**
     * Resolve and validate a coupon code against the given subtotal.
     *
     * @return array{coupon: ?Coupon, discount_amount: float, error: ?string}
     */
    public function resolveCoupon(?string $code, float $subtotal): array
    {
        if ($code === null || trim($code) === '') {
            return ['coupon' => null, 'discount_amount' => 0.0, 'error' => null];
        }

        $normalized = Coupon::normalizeCode($code);
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$normalized])->first();

        if (! $coupon) {
            return ['coupon' => null, 'discount_amount' => 0.0, 'error' => __('Invalid coupon code.')];
        }

        if ($error = $coupon->validateForSubtotal($subtotal)) {
            return ['coupon' => null, 'discount_amount' => 0.0, 'error' => $error];
        }

        return [
            'coupon' => $coupon,
            'discount_amount' => $coupon->calculateDiscount($subtotal),
            'error' => null,
        ];
    }

    private function outOfStockMessage(Property $property): string
    {
        $attributes = $property->variantAttributes->map(fn ($attr) => $attr->value)->implode(' - ');
        $productName = $property->product?->name ?? __('Product');

        return $property->stock <= 0 || $property->trashed()
            ? __('The product :name (:attributes) is out of stock (0)', ['name' => $productName, 'attributes' => $attributes])
            : __('The product :name (:attributes) is out of stock', ['name' => $productName, 'attributes' => $attributes]);
    }
}
