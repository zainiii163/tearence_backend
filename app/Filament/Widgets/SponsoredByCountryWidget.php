<?php

namespace App\Filament\Widgets;

use App\Models\SponsoredAdvert;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

/**
 * Clive: country counters for sponsored ads — Filament admin only.
 */
class SponsoredByCountryWidget extends ChartWidget
{
    protected static ?string $heading = 'Sponsored adverts by country';

    protected static ?int $sort = 4;

    protected static bool $isDiscovered = false;

    protected static ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = SponsoredAdvert::query()
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
                    'backgroundColor' => '#f59e0b',
                ],
                [
                    'label' => 'Views',
                    'data' => $rows->pluck('views')->map(fn ($v) => (int) $v)->all(),
                    'backgroundColor' => '#0ea5e9',
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
