<?php

namespace App\Filament\Widgets;

use App\Models\PromotedAdvert;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Clive: country counters for promoted ads — Filament admin only.
 */
class PromotedByCountryWidget extends ChartWidget
{
    protected static ?string $heading = 'Promoted adverts by country';

    protected static ?int $sort = 2;

    protected static bool $isDiscovered = false;

    protected static ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = PromotedAdvert::query()
            ->select('country', DB::raw('COUNT(*) as total'), DB::raw('SUM(COALESCE(views_count, 0)) as views'))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Adverts',
                    'data' => $rows->pluck('total')->all(),
                    'backgroundColor' => '#f97316',
                ],
                [
                    'label' => 'Views',
                    'data' => $rows->pluck('views')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => '#6366f1',
                ],
            ],
            'labels' => $rows->pluck('country')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
