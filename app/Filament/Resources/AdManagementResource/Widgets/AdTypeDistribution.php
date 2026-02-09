<?php

namespace App\Filament\Resources\AdManagementResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AdTypeDistribution extends ChartWidget
{
    protected static ?string $heading = 'Ad Type Distribution';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = DB::table('advertisements')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Ads by Type',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(251, 146, 60, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('type')->map(fn ($type) => ucfirst($type))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
        ];
    }
}
