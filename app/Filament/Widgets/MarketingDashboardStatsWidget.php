<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketingDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $sponsored = DashboardMetrics::count('sponsored_adverts', function ($q) {
            if (DashboardMetrics::columnExists('sponsored_adverts', 'status')) {
                $q->where('status', 'active');
            }
        });
        $promoted = DashboardMetrics::count('promoted_adverts', function ($q) {
            if (DashboardMetrics::columnExists('promoted_adverts', 'status')) {
                $q->where('status', 'active');
            }
        });
        $featured = DashboardMetrics::count('featured_adverts', function ($q) {
            if (DashboardMetrics::columnExists('featured_adverts', 'status')) {
                $q->where('status', 'active');
            }
        });
        $banners = DashboardMetrics::count('banner_ads') ?: DashboardMetrics::count('banner');
        $affiliateLinks = DashboardMetrics::count('affiliate_links', function ($q) {
            if (DashboardMetrics::columnExists('affiliate_links', 'is_active')) {
                $q->where('is_active', 1);
            }
        });
        $affiliateApps = DashboardMetrics::count('affiliate_applications');
        $campaigns = DashboardMetrics::count('campaign') ?: DashboardMetrics::count('campaigns');

        $views = DashboardMetrics::sum('sponsored_adverts', 'views_count')
            + DashboardMetrics::sum('promoted_adverts', 'views_count')
            + DashboardMetrics::sum('featured_adverts', 'views_count');

        return [
            Stat::make('Paid Placements', number_format($sponsored + $promoted + $featured))
                ->description('Sponsored · promoted · featured')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
            Stat::make('Banner Inventory', number_format($banners))
                ->description('Active banner creatives')
                ->descriptionIcon('heroicon-m-photo')
                ->color('info'),
            Stat::make('Affiliate Pipeline', number_format($affiliateLinks + $affiliateApps))
                ->description(number_format($affiliateLinks) . ' live links · ' . number_format($affiliateApps) . ' apps')
                ->descriptionIcon('heroicon-m-link')
                ->color('success'),
            Stat::make('Ad Impressions', number_format($views))
                ->description($campaigns ? number_format($campaigns) . ' campaigns tracked' : 'Across paid ad products')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),
        ];
    }
}
