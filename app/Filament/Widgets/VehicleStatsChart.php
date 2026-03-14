<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VehicleStatsChart extends ChartWidget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Vehicle Statistics (Last 7 Days)';
    
    protected static ?array $options = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'scales' => [
            'y' => [
                'beginAtZero' => true,
            ],
        ],
    ];

    protected function getData(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->format('Y-m-d');
        });

        $vehiclesPerDay = Vehicle::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $viewsPerDay = Vehicle::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(views_count) as total_views')
            )
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_views', 'date')
            ->toArray();

        $labels = $last7Days->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('M j');
        })->toArray();

        $vehiclesData = $last7Days->map(function ($date) use ($vehiclesPerDay) {
            return $vehiclesPerDay[$date] ?? 0;
        })->toArray();

        $viewsData = $last7Days->map(function ($date) use ($viewsPerDay) {
            return $viewsPerDay[$date] ?? 0;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'New Vehicles',
                    'data' => $vehiclesData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Total Views',
                    'data' => $viewsData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
