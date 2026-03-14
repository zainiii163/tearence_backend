<?php

namespace App\Filament\Widgets;

use App\Models\FundingProject;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class FundingChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Funding Projects Trend';
    
    protected static ?int $sort = 2;
    
    protected static string $color = 'primary';
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $data = Trend::model(FundingProject::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Projects Created',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
