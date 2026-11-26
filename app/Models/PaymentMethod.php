<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'is_active',
        'sort_order',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'config' => 'array',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'en'
            ? ($this->name_en ?: $this->name_ar)
            : ($this->name_ar ?: $this->name_en);
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'en'
            ? ($this->description_en ?: $this->description_ar)
            : ($this->description_ar ?: $this->description_en);
    }

    /**
     * Active payment method codes configured in the dashboard.
     *
     * @return array<int, string>
     */
    public static function activeCodes(): array
    {
        $codes = self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();

        return $codes !== [] ? $codes : ['cash', 'credit_card', 'vodafone_cash', 'fawry'];
    }
}
