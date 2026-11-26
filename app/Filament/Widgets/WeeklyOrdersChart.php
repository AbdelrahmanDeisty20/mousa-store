<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class WeeklyOrdersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected string $color = 'primary';

    public function getHeading(): string | Htmlable | null
    {
        return __('Weekly Orders');
    }

    public function getDescription(): string | Htmlable | null
    {
        return __('Orders count this week');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $days = [
            'Monday' => __('Mon'),
            'Tuesday' => __('Tue'),
            'Wednesday' => __('Wed'),
            'Thursday' => __('Thu'),
            'Friday' => __('Fri'),
            'Saturday' => __('Sat'),
            'Sunday' => __('Sun'),
        ];

        $counts = array_fill_keys(array_keys($days), 0);

        Order::query()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get(['created_at'])
            ->each(function (Order $order) use (&$counts): void {
                $day = $order->created_at?->format('l');
                if ($day && array_key_exists($day, $counts)) {
                    $counts[$day]++;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => __('Orders'),
                    'data' => array_values($counts),
                ],
            ],
            'labels' => array_values($days),
        ];
    }
}
