<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'price',
        'main_image',
        'images',
        'stock'
    ];

    protected $appends = [
        'image_path',
        'images_path',
    ];

    protected $hidden = ['main_image', 'images'];

    protected $casts = [
        'images' => 'array',
        'price' => 'float',
        'stock' => 'integer',
    ];

    protected static function booted()
    {
        $autoDelete = function ($property) {
            if ($property->stock <= 0) {
                $property->delete();
            }
        };

        static::updated($autoDelete);
        static::created($autoDelete);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function getImagePathAttribute()
    {
        if (!$this->main_image)
            return asset('dashboard/assets/img/placeholder.png');

        if (filter_var($this->main_image, FILTER_VALIDATE_URL)) {
            return $this->main_image;
        }

        return asset('storage/products/' . $this->main_image);
    }

    public function getImagesPathAttribute()
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }

        return collect($this->images)->map(function ($img) {
            if (filter_var($img, FILTER_VALIDATE_URL)) {
                return $img;
            }
            return asset('storage/products/' . $img);
        })->toArray();
    }

    public function variantAttributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function cart()
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot('quantity', 'line_total', 'unit_price')->withTimestamps();
    }

    public function orders()
    {
        return $this
            ->belongsToMany(Order::class, 'orders_items', 'property_id', 'order_id')
            ->withPivot(['quantity', 'price']);
    }
}
