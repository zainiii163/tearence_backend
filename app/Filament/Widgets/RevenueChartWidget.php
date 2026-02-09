<?php

namespace App\Filament\Widgets;

use App\Models\RevenueTracking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ad Revenue Trends (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $labels = [];
        $bannerRevenue = [];
        $affiliateRevenue = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            
            $bannerRevenue[] = RevenueTracking::where('ad_type', 'banner')
                ->where('payment_status', 'completed')
                ->whereDate('created_at', $dateStr)
                ->sum('amount');
                
            $affiliateRevenue[] = RevenueTracking::where('ad_type', 'affiliate')
                ->where('payment_status', 'completed')
                ->whereDate('created_at', $dateStr)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Banner Revenue',
                    'data' => $bannerRevenue,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Affiliate Revenue',
                    'data' => $affiliateRevenue,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toFixed(2); }',
                    ],
                ],
            ],
        ];
    }
}

