<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class MarketingMixChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Marketing mix — active inventory';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $sponsored = DashboardMetrics::count('sponsored_adverts');
        $promoted = DashboardMetrics::count('promoted_adverts');
        $featured = DashboardMetrics::count('featured_adverts');
        $banners = DashboardMetrics::count('banner_ads') ?: DashboardMetrics::count('banner');
        $affiliates = DashboardMetrics::count('affiliate_links');

        return [
            'datasets' => [
                [
                    'label' => 'Listings',
                    'data' => [$sponsored, $promoted, $featured, $banners, $affiliates],
                    'backgroundColor' => [
                        '#f59e0b',
                        '#8b5cf6',
                        '#3b82f6',
                        '#06b6d4',
                        '#10b981',
                    ],
                ],
            ],
            'labels' => ['Sponsored', 'Promoted', 'Featured', 'Banners', 'Affiliate links'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
