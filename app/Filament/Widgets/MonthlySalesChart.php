<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class MonthlySalesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected string $color = 'success';

    public function getHeading(): string | Htmlable | null
    {
        return __('Monthly Sales');
    }

    public function getDescription(): string | Htmlable | null
    {
        return __('Total sales per month');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = [
            1 => __('Jan'),
            2 => __('Feb'),
            3 => __('Mar'),
            4 => __('Apr'),
            5 => __('May'),
            6 => __('Jun'),
            7 => __('Jul'),
            8 => __('Aug'),
            9 => __('Sep'),
            10 => __('Oct'),
            11 => __('Nov'),
            12 => __('Dec'),
        ];

        $totals = array_fill(1, 12, 0.0);

        Order::query()
            ->whereYear('created_at', now()->year)
            ->get(['created_at', 'total_price'])
            ->each(function (Order $order) use (&$totals): void {
                $month = $order->created_at?->month;
                if ($month && array_key_exists($month, $totals)) {
                    $totals[$month] += (float) $order->total_price;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => __('Sales'),
                    'data' => array_values($totals),
                    'fill' => 'start',
                ],
            ],
            'labels' => array_values($months),
        ];
    }
}
