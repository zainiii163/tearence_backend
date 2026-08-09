<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class SalesTrendChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Sales volume (14 days)';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $buySell = DashboardMetrics::dailySumSeries(
            'buy_sell_purchases',
            'price',
            'created_at',
            14,
            fn ($q) => $q->where('payment_status', 'paid')
        );
        $templates = DashboardMetrics::dailySumSeries('template_purchases', 'amount', 'created_at', 14);
        $banners = DashboardMetrics::dailySumSeries('banner_purchases', 'amount', 'created_at', 14);

        $combined = [];
        foreach ($buySell['values'] as $i => $v) {
            $combined[] = (float) $v + (float) ($templates['values'][$i] ?? 0) + (float) ($banners['values'][$i] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Paid sales ($)',
                    'data' => $combined,
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $buySell['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
