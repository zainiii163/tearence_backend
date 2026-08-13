<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateApplication;
use App\Models\AffiliateHopConversion;
use App\Models\AffiliatePayout;
use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $liveOffers = 0;
        $userPosts = 0;
        $pendingApps = 0;
        $approvedApps = 0;
        $pendingPayouts = 0.0;
        $hopCommission = 0.0;

        try {
            $liveOffers = BusinessAffiliateOffer::query()
                ->where('status', 'approved')
                ->where('is_active', true)
                ->count();
            $userPosts = UserAffiliatePost::count();
            $pendingApps = AffiliateApplication::where('status', 'pending')->count();
            $approvedApps = AffiliateApplication::where('status', 'approved')->count();
        } catch (\Throwable) {
            // ignore
        }

        try {
            $pendingPayouts = (float) AffiliatePayout::query()
                ->whereIn('status', ['pending', 'processing'])
                ->sum('amount');
        } catch (\Throwable) {
            // table may not exist yet
        }

        try {
            $hopCommission = (float) AffiliateHopConversion::query()->sum('commission_amount');
        } catch (\Throwable) {
            // ignore
        }

        return [
            Stat::make('Live marketplace offers', number_format($liveOffers))
                ->description('Approved & active programs')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            Stat::make('Promoters approved', number_format($approvedApps))
                ->description($pendingApps . ' pending applications')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Hop commissions', '$' . number_format($hopCommission, 2))
                ->description('Recorded hop-link conversions')
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),

            Stat::make('Payouts queue', '$' . number_format($pendingPayouts, 2))
                ->description('Pending / processing requests')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Link ads / user posts', number_format($userPosts))
                ->description('Promoter link listings')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
