<?php

namespace App\Service;

use App\Filament\Resources\ProductResource;
use App\Models\Property;
use Filament\Support\Icons\Heroicon;

class ProductHelper
{
    public static function getAttributesString($property, $lang = 'ar')
    {
        $property->loadMissing('variantAttributes');
        if ($property->variantAttributes->isEmpty()) {
            return '';
        }

        return $property->variantAttributes->map(function ($attr) use ($lang) {
            $key = $attr->{"key_{$lang}"} ?? $attr->key_en;
            $value = $attr->{"value_{$lang}"} ?? $attr->value_en;

            return "{$key}: {$value}";
        })->implode(', ');
    }

    public static function checkProductQuantity($property)
    {
        // Trigger only at specific thresholds: 10 (Low) or 0 (Out of stock)
        if ($property->stock != 10 && $property->stock != 0) {
            return;
        }

        $property->loadMissing(['product', 'variantAttributes']);
        if (! $property->product) {
            return;
        }

        $attributesAr = self::getAttributesString($property, 'ar');
        $attributesEn = self::getAttributesString($property, 'en');
        $attrStringAr = $attributesAr ? " ({$attributesAr})" : '';
        $attrStringEn = $attributesEn ? " ({$attributesEn})" : '';

        if ($property->stock == 10) {
            $title = 'كمية المنتج منخفضة / Product Low Stock';
            $body = "المنتج {$property->product->name_ar}{$attrStringAr} أوشكت كميته على النفاد (المتبقي 10) | Product {$property->product->name_en}{$attrStringEn} is low on stock (10 remaining)";
            $type = 'low_stock_admin';
            $status = 'warning';
        } else {
            $title = 'نفدت كمية المنتج / Product Out of Stock';
            $body = "نفدت كمية المنتج {$property->product->name_ar}{$attrStringAr} تماماً | Product {$property->product->name_en}{$attrStringEn} is now out of stock";
            $type = 'out_of_stock_admin';
            $status = 'danger';
        }

        $url = rescue(
            fn (): string => ProductResource::getUrl('edit', ['record' => $property->product_id], panel: 'admin'),
            report: false
        );

        AdminNotificationDispatcher::notifyAdmins(
            title: $title,
            body: $body,
            type: $type,
            data: [
                'property_id' => $property->id,
                'product_id' => $property->product_id,
                'stock' => $property->stock,
            ],
            actionLabel: __('Edit Product'),
            actionUrl: $url,
            status: $status,
            icon: Heroicon::OutlinedExclamationTriangle,
        );
    }
}
