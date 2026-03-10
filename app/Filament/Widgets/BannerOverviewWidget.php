<?php

namespace App\Filament\Widgets;

use App\Models\BannerAd;
use App\Models\BannerCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BannerOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalBanners = BannerAd::count();
        $activeBanners = BannerAd::where('status', 'active')->where('is_active', true)->count();
        $pendingBanners = BannerAd::where('status', 'pending')->count();
        $totalViews = BannerAd::sum('views_count');
        $totalClicks = BannerAd::sum('clicks_count');
        $totalCategories = BannerCategory::where('is_active', true)->count();

        // Calculate average CTR
        $avgCtr = $totalViews > 0 ? ($totalClicks / $totalViews) * 100 : 0;

        return [
            Stat::make('Total Banners', number_format($totalBanners))
                ->description('All banner advertisements')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),

            Stat::make('Active Banners', number_format($activeBanners))
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 8, 12, 15, 18, 20, 22]),

            Stat::make('Pending Review', number_format($pendingBanners))
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([3, 5, 4, 6, 5, 8, 7]),

            Stat::make('Total Views', number_format($totalViews))
                ->description('All-time views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info')
                ->chart([100, 200, 150, 300, 250, 400, 350]),

            Stat::make('Total Clicks', number_format($totalClicks))
                ->description('All-time clicks')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('danger')
                ->chart([10, 25, 20, 35, 30, 45, 40]),

            Stat::make('Avg CTR', number_format($avgCtr, 2) . '%')
                ->description('Click-through rate')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('purple')
                ->chart([2.1, 2.5, 2.3, 3.1, 2.8, 3.5, 3.2]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
