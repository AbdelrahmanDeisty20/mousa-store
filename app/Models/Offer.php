<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    // الحقول المسموح بالملأ الجماعي
    protected $fillable = [
        'start',
        'end',
        'property_id',
        'discount_price',
        'discount',
    ];

    protected $appends = ['disscount_price'];

    protected $casts = [
        'discount_price' => 'float',
        'discount' => 'float',
    ];

    public function getDisscountPriceAttribute()
    {
        return $this->attributes['disscount_price']
            ?? $this->attributes['discount_price']
            ?? $this->attributes['discount']
            ?? 0;
    }

    /**
     * العلاقة مع المنتج
     * كل عرض ينتمي لمنتج واحد
     */
    public function variant()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * إرجاع عنوان العرض حسب اللغة الحالية
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();

        return $this->{"title_{$locale}"} ?? $this->title_en;
    }

    /**
     * إرجاع وصف العرض حسب اللغة الحالية
     */
    public function getDescAttribute()
    {
        $locale = app()->getLocale();

        return $this->{"desc_{$locale}"} ?? $this->desc_en;
    }
    
}