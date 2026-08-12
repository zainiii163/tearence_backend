<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use App\Models\AffiliateApplication;
use App\Models\BusinessAffiliateOffer;
use App\Models\CustomerBusiness;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class BusinessDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $businesses = Schema::hasTable('customer_business')
            ? CustomerBusiness::count()
            : 0;
        $offers = Schema::hasTable('business_affiliate_offers')
            ? BusinessAffiliateOffer::count()
            : 0;
        $pendingApps = Schema::hasTable('affiliate_applications')
            ? AffiliateApplication::where('status', 'pending')->count()
            : 0;
        $approvedApps = Schema::hasTable('affiliate_applications')
            ? AffiliateApplication::where('status', 'approved')->count()
            : 0;
        $listings = DashboardMetrics::count('vehicles')
            + DashboardMetrics::count('property_adverts')
            + DashboardMetrics::count('buy_sell_adverts')
            + DashboardMetrics::count('job_adverts');

        return [
            Stat::make('Business pages', number_format($businesses))
                ->description('Registered business profiles')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Marketplace listings', number_format($listings))
                ->description('Vehicles · property · jobs · buy/sell')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),
            Stat::make('Affiliate offers', number_format($offers))
                ->description(number_format($pendingApps) . ' pending applications')
                ->descriptionIcon('heroicon-m-link')
                ->color('success'),
            Stat::make('Approved promoters', number_format($approvedApps))
                ->description('Hop links minted')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
        ];
    }
}
