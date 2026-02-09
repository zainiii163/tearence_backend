<?php

namespace App\Filament\Resources\AdManagementResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = DB::table('advertisements')
            ->select([
                DB::raw('COUNT(*) as total_ads'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ads'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_payments'),
                DB::raw('SUM(CASE WHEN end_date < NOW() THEN 1 ELSE 0 END) as expired_ads'),
            ])
            ->first();

        return [
            Stat::make('Total Ads', $stats->total_ads ?? 0)
                ->description('All advertisements')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),
            
            Stat::make('Active Ads', $stats->active_ads ?? 0)
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success')
                ->chart([5, 8, 7, 10, 12, 14, 16]),
            
            Stat::make('Total Revenue', '$' . number_format($stats->total_revenue ?? 0, 2))
                ->description('Paid advertisements')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([100, 200, 150, 300, 250, 400, 350]),
            
            Stat::make('Pending Payments', $stats->pending_payments ?? 0)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([2, 3, 1, 4, 2, 3, 1]),
            
            Stat::make('Expired Ads', $stats->expired_ads ?? 0)
                ->description('Need attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([1, 2, 3, 2, 4, 3, 2]),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
