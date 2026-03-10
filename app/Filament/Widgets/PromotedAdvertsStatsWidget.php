<?php

namespace App\Filament\Widgets;

use App\Models\PromotedAdvert;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PromotedAdvertsStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Promoted Adverts Trends';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = PromotedAdvert::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        foreach ($data as $item) {
            $labels[] = $item->date;
            $values[] = $item->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Promoted Adverts Created',
                    'data' => $values,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor' => 'rgba(251, 191, 36, 1)',
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
}
