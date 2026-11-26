<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PaymentMethod $method): array => [
                'id' => $method->id,
                'code' => $method->code,
                'name' => $method->name,
                'name_ar' => $method->name_ar,
                'name_en' => $method->name_en,
                'description' => $method->description,
                'is_active' => $method->is_active,
                'sort_order' => $method->sort_order,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => $methods,
        ]);
    }
}
