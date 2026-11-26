<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todaySales = (float) Order::whereDate('created_at', today())->sum('total_price');
        $customersCount = User::where('user_type', 'user')->where('is_verfived', true)->count();

        $topProductToday = Product::select(
            'products.id',
            'products.name_ar',
            'products.name_en',
            DB::raw('SUM(orders_items.quantity) as total_qty')
        )
            ->join('properties', 'products.id', '=', 'properties.product_id')
            ->join('orders_items', 'properties.id', '=', 'orders_items.property_id')
            ->join('orders', 'orders.id', '=', 'orders_items.order_id')
            ->whereDate('orders.created_at', today())
            ->groupBy('products.id', 'products.name_ar', 'products.name_en')
            ->orderByDesc('total_qty')
            ->first();

        $totalProducts = Product::count();
        $pendingOrders = Order::where('status', 'pending')->count();

        return [
            Stat::make(__('Today Orders'), (string) $todayOrders)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('primary'),

            Stat::make(__('Today Sales'), number_format($todaySales, 2) . ' ' . __('EGP'))
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),

            Stat::make(__('Customers'), (string) $customersCount)
                ->icon(Heroicon::OutlinedUsers)
                ->color('info'),

            Stat::make(__('Top Product Today'), $topProductToday?->name ?? '—')
                ->icon(Heroicon::OutlinedStar)
                ->color('warning'),

            Stat::make(__('Total Products'), (string) $totalProducts)
                ->icon(Heroicon::OutlinedCube)
                ->color('gray'),

            Stat::make(__('Pending Orders'), (string) $pendingOrders)
                ->icon(Heroicon::OutlinedClock)
                ->color($pendingOrders > 0 ? 'danger' : 'success'),
        ];
    }
}
