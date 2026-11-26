<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_price',
        'subtotal',
        'coupon_id',
        'coupon_code',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'status',
        'user_name',
        'user_phone',
        'user_email',
        'user_address',
        'marketing_opt_in',
        'governorate_id',
        'notice',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'shipping_amount' => 'float',
        'total_price' => 'float',
        'marketing_opt_in' => 'boolean',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this
            ->belongsToMany(Property::class, 'orders_items', 'order_id', 'property_id')
            ->withTrashed()
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
