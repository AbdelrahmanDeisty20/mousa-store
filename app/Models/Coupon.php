<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_order_amount' => 'float',
        'max_discount_amount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = self::normalizeCode($value);
    }

    /**
     * Validate coupon status, window, usage limit, and minimum order amount.
     */
    public function validateForSubtotal(float $subtotal): ?string
    {
        if (! $this->is_active) {
            return __('This coupon is currently inactive.');
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return __('This coupon is not active yet.');
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return __('This coupon has expired.');
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return __('This coupon usage limit has been reached.');
        }

        if ($this->min_order_amount !== null && $this->min_order_amount > 0 && $subtotal < $this->min_order_amount) {
            return __('Order total does not meet the minimum amount (:min EGP) for this coupon.', [
                'min' => $this->min_order_amount,
            ]);
        }

        return null;
    }

    /**
     * Calculate the discount amount for the given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $type = strtolower((string) $this->discount_type);

        if (in_array($type, ['percent', 'percentage'], true)) {
            $discount = ($subtotal * (float) $this->discount_value) / 100;
            if ($this->max_discount_amount !== null && $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }

            return round(min($discount, $subtotal), 2);
        }

        return round(min((float) $this->discount_value, $subtotal), 2);
    }
}
