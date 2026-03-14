<?php

namespace App\Filament\Widgets;

use App\Models\FeaturedAdvert;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class FeaturedAdvertsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Featured Adverts Analytics';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Last 30 days data
        $days = 30;
        $data = [];
        $labels = [];
        $revenueData = [];
        $viewsData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            
            // Featured adverts created per day
            $createdCount = FeaturedAdvert::whereDate('created_at', $date)->count();
            $data[] = $createdCount;
            
            // Revenue per day
            $revenue = FeaturedAdvert::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('upsell_price');
            $revenueData[] = $revenue;
            
            // Views per day (approximate)
            $views = FeaturedAdvert::whereDate('created_at', '<=', $date)
                ->where('is_active', true)
                ->avg('view_count') ?? 0;
            $viewsData[] = round($views, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Featured Adverts Created',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Daily Revenue (£)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
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
            'responsive' => true,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Adverts Created',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (£)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date',
                    ],
                ],
            ],
        ];
    }
}
