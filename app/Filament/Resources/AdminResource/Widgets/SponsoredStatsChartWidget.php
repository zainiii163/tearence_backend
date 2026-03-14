<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\SponsoredAdvert;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SponsoredStatsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Sponsored Adverts Statistics';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get last 30 days of data
        $data = DB::table('sponsored_adverts')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_ads,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_ads,
                SUM(views) as total_views,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_ads,
                SUM(CASE WHEN promoted = 1 THEN 1 ELSE 0 END) as promoted_ads,
                SUM(CASE WHEN sponsored = 1 THEN 1 ELSE 0 END) as sponsored_ads
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Adverts',
                    'data' => $data->pluck('total_ads'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                ],
                [
                    'label' => 'Active Adverts',
                    'data' => $data->pluck('active_ads'),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                ],
                [
                    'label' => 'Total Views',
                    'data' => $data->pluck('total_views'),
                    'backgroundColor' => 'rgba(168, 85, 247, 0.2)',
                    'borderColor' => 'rgba(168, 85, 247, 1)',
                ],
                [
                    'label' => 'Featured Ads',
                    'data' => $data->pluck('featured_ads'),
                    'backgroundColor' => 'rgba(251, 146, 60, 0.2)',
                    'borderColor' => 'rgba(251, 146, 60, 1)',
                ],
                [
                    'label' => 'Promoted Ads',
                    'data' => $data->pluck('promoted_ads'),
                    'backgroundColor' => 'rgba(147, 51, 234, 0.2)',
                    'borderColor' => 'rgba(147, 51, 234, 1)',
                ],
                [
                    'label' => 'Sponsored Ads',
                    'data' => $data->pluck('sponsored_ads'),
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                ],
            ],
            'labels' => $data->pluck('date'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
