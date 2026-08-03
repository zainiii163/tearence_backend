<?php

namespace App\Filament\Widgets;

use App\Services\CrossPromotionFeedService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Cross-site sponsored / promoted / featured feed totals for super admin.
 * Clive: public pages are feeds only — counters live here.
 */
class CrossSitePromotionFeedWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $service = app(CrossPromotionFeedService::class);

        $sponsored = ['total' => 0, 'countries' => []];
        $promoted = ['total' => 0, 'countries' => []];
        $featured = ['total' => 0, 'countries' => []];

        try {
            $sponsored = $service->adminSnapshot('sponsored', 3);
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            $promoted = $service->adminSnapshot('promoted', 3);
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            $featured = $service->adminSnapshot('featured', 3);
        } catch (\Throwable $e) {
            // ignore
        }

        $topSponsored = collect($sponsored['countries'] ?? [])->pluck('name')->take(2)->implode(', ') ?: '—';
        $topPromoted = collect($promoted['countries'] ?? [])->pluck('name')->take(2)->implode(', ') ?: '—';
        $topFeatured = collect($featured['countries'] ?? [])->pluck('name')->take(2)->implode(', ') ?: '—';

        return [
            Stat::make('Sponsored site feed', number_format((int) ($sponsored['total'] ?? 0)))
                ->description('Top countries: ' . $topSponsored)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            Stat::make('Promoted site feed', number_format((int) ($promoted['total'] ?? 0)))
                ->description('Top countries: ' . $topPromoted)
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary'),

            Stat::make('Featured site feed', number_format((int) ($featured['total'] ?? 0)))
                ->description('Top countries: ' . $topFeatured)
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }
}
