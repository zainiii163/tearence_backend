<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class FinancialTrendChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Financial inflow (14 days)';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $dateCol = DashboardMetrics::columnExists('revenue_tracking', 'payment_date')
            ? 'payment_date'
            : 'created_at';

        $revenue = DashboardMetrics::dailySumSeries(
            'revenue_tracking',
            'amount',
            $dateCol,
            14,
            function ($q) {
                if (DashboardMetrics::columnExists('revenue_tracking', 'status')) {
                    $q->where('status', 'completed');
                }
            }
        );
        $donations = DashboardMetrics::dailySumSeries('donation_contributions', 'amount', 'created_at', 14);

        return [
            'datasets' => [
                [
                    'label' => 'Platform revenue',
                    'data' => $revenue['values'],
                    'borderColor' => '#d97706',
                    'backgroundColor' => 'rgba(217, 119, 6, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Donations',
                    'data' => $donations['values'],
                    'borderColor' => '#e11d48',
                    'backgroundColor' => 'rgba(225, 29, 72, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $revenue['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
