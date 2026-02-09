<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class JobsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Jobs Posted (Last 30 Days)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = Listing::whereDate('created_at', $date->format('Y-m-d'))->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jobs Posted',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

