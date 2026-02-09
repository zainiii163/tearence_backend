<?php

namespace App\Filament\Widgets;

use App\Models\Banner;
use App\Models\Affiliate;
use App\Models\RevenueTracking;
use App\Models\AdPricingPlan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MonetizationOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalBannerRevenue = RevenueTracking::where('ad_type', 'banner')
            ->where('payment_status', 'completed')
            ->sum('amount');

        $totalAffiliateRevenue = RevenueTracking::where('ad_type', 'affiliate')
            ->where('payment_status', 'completed')
            ->sum('amount');

        $activeBanners = Banner::where('is_active', true)
            ->count();

        $activeAffiliates = Affiliate::where('is_active', true)
            ->count();

        $pendingPayments = RevenueTracking::whereIn('ad_type', ['banner', 'affiliate'])
            ->where('payment_status', 'pending')
            ->count();

        $totalRevenue = $totalBannerRevenue + $totalAffiliateRevenue;

        return [
            Stat::make('Total Ad Revenue', '$' . number_format($totalRevenue, 2))
                ->description('Total revenue from banner and affiliate ads')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Banner Revenue', '$' . number_format($totalBannerRevenue, 2))
                ->description('Revenue from banner advertisements')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Affiliate Revenue', '$' . number_format($totalAffiliateRevenue, 2))
                ->description('Revenue from affiliate advertisements')
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),

            Stat::make('Active Ads', $activeBanners + $activeAffiliates)
                ->description($activeBanners . ' banners, ' . $activeAffiliates . ' affiliates')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),

            Stat::make('Pending Payments', $pendingPayments)
                ->description('Payments awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
