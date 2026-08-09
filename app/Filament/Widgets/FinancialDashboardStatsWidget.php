<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $platformRevenue = DashboardMetrics::sum('revenue_tracking', 'amount', function ($q) {
            if (DashboardMetrics::columnExists('revenue_tracking', 'status')) {
                $q->where('status', 'completed');
            }
        });
        $monthRevenue = DashboardMetrics::sum('revenue_tracking', 'amount', function ($q) {
            if (DashboardMetrics::columnExists('revenue_tracking', 'status')) {
                $q->where('status', 'completed');
            }
            if (DashboardMetrics::columnExists('revenue_tracking', 'payment_date')) {
                $q->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year);
            } elseif (DashboardMetrics::columnExists('revenue_tracking', 'created_at')) {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        });

        $donations = DashboardMetrics::sum('donation_contributions', 'amount', function ($q) {
            if (DashboardMetrics::columnExists('donation_contributions', 'payment_status')) {
                $q->whereIn('payment_status', ['paid', 'completed', 'success']);
            }
        });
        if ($donations <= 0) {
            $donations = DashboardMetrics::sum('donation_contributions', 'amount');
        }

        $funding = DashboardMetrics::sum('funding_pledges', 'amount', function ($q) {
            if (DashboardMetrics::columnExists('funding_pledges', 'status')) {
                $q->whereIn('status', ['paid', 'completed', 'confirmed']);
            }
        });
        if ($funding <= 0) {
            $funding = DashboardMetrics::sum('funding_pledges', 'amount');
        }

        $pending = DashboardMetrics::sum('revenue_tracking', 'amount', function ($q) {
            if (DashboardMetrics::columnExists('revenue_tracking', 'status')) {
                $q->whereIn('status', ['pending', 'processing']);
            }
        });

        return [
            Stat::make('Platform Revenue', DashboardMetrics::money($platformRevenue))
                ->description('Completed tracked revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('This Month', DashboardMetrics::money($monthRevenue))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make('Donations & Funding', DashboardMetrics::money($donations + $funding))
                ->description(DashboardMetrics::money($donations) . ' donations · ' . DashboardMetrics::money($funding) . ' pledges')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
            Stat::make('Pending Settlements', DashboardMetrics::money($pending))
                ->description('Awaiting completion')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
