<?php

namespace App\Filament\Widgets;

use App\Models\RevenueTracking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = RevenueTracking::completed()
            ->sum('amount');
        
        $todayRevenue = RevenueTracking::completed()
            ->whereDate('payment_date', today())
            ->sum('amount');
        
        $monthRevenue = RevenueTracking::completed()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        
        $weekRevenue = RevenueTracking::completed()
            ->whereBetween('payment_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('amount');

        return [
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('All time')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueChart(30)),
            Stat::make('This Month', '$' . number_format($monthRevenue, 2))
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->chart($this->getRevenueChart(30)),
            Stat::make('This Week', '$' . number_format($weekRevenue, 2))
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart($this->getRevenueChart(7)),
            Stat::make('Today', '$' . number_format($todayRevenue, 2))
                ->description('Today\'s earnings')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning')
                ->chart($this->getRevenueChart(1)),
        ];
    }

    protected function getRevenueChart(int $days): array
    {
        $revenues = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenues[] = RevenueTracking::completed()
                ->whereDate('payment_date', $date->format('Y-m-d'))
                ->sum('amount');
        }
        return $revenues;
    }
}

