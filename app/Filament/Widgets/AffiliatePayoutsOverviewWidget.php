<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateApplication;
use App\Models\AffiliateConversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Clive: affiliate payouts belong under Dashboard (not a half-finished affiliates dump).
 */
class AffiliatePayoutsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $pendingCommission = 0.0;
        $confirmedCommission = 0.0;
        $pendingApps = 0;
        $approvedApps = 0;

        try {
            $pendingCommission = (float) AffiliateConversion::pending()->sum('commission_amount');
            $confirmedCommission = (float) AffiliateConversion::confirmed()->sum('commission_amount');
        } catch (\Throwable $e) {
            // table may be empty / missing in some envs
        }

        try {
            $pendingApps = AffiliateApplication::pending()->count();
            $approvedApps = AffiliateApplication::approved()->count();
        } catch (\Throwable $e) {
            // ignore
        }

        return [
            Stat::make('Pending payouts', '$' . number_format($pendingCommission, 2))
                ->description('Commission awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Confirmed commissions', '$' . number_format($confirmedCommission, 2))
                ->description('Ready / paid commissions')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending applications', number_format($pendingApps))
                ->description('Affiliate applications to review')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('info'),

            Stat::make('Approved affiliates', number_format($approvedApps))
                ->description('Active approved applications')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
        ];
    }
}
