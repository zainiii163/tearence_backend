<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateApplication;
use App\Models\AffiliateHopConversion;
use App\Models\AffiliatePayout;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Dashboard cluster: affiliate payouts & hop earnings overview.
 */
class AffiliatePayoutsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $pendingPayouts = 0.0;
        $paidPayouts = 0.0;
        $hopCommission = 0.0;
        $pendingApps = 0;
        $approvedApps = 0;

        try {
            $pendingPayouts = (float) AffiliatePayout::query()
                ->whereIn('status', ['pending', 'processing'])
                ->sum('amount');
            $paidPayouts = (float) AffiliatePayout::query()
                ->where('status', 'paid')
                ->sum('amount');
        } catch (\Throwable) {
            // migration may not have run
        }

        try {
            $hopCommission = (float) AffiliateHopConversion::query()->sum('commission_amount');
        } catch (\Throwable) {
            // ignore
        }

        try {
            $pendingApps = AffiliateApplication::pending()->count();
            $approvedApps = AffiliateApplication::approved()->count();
        } catch (\Throwable) {
            // ignore
        }

        return [
            Stat::make('Pending payouts', '$' . number_format($pendingPayouts, 2))
                ->description('Affiliate payout requests awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Paid out', '$' . number_format($paidPayouts, 2))
                ->description('Completed payout requests')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Hop commissions', '$' . number_format($hopCommission, 2))
                ->description('Total recorded hop-link commissions')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('Pending applications', number_format($pendingApps))
                ->description(number_format($approvedApps) . ' approved promoters')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('primary'),
        ];
    }
}
