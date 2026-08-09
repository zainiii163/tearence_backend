<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $staff = DashboardMetrics::count('users', function ($q) {
            $q->where(function ($inner) {
                if (DashboardMetrics::columnExists('users', 'is_super_admin')) {
                    $inner->orWhere('is_super_admin', 1);
                }
                if (DashboardMetrics::columnExists('users', 'is_admin')) {
                    $inner->orWhere('is_admin', 1);
                }
                if (DashboardMetrics::columnExists('users', 'role')) {
                    $inner->orWhereIn('role', ['admin', 'super_admin', 'staff', 'moderator', 'manager']);
                }
            });
        });

        $totalUsers = DashboardMetrics::count('users');
        $customers = max($totalUsers - $staff, 0);

        $jobs = DashboardMetrics::count('jobs');
        $applications = DashboardMetrics::count('job_applications');
        $seekers = DashboardMetrics::count('job_seekers');
        $groups = DashboardMetrics::count('group');

        return [
            Stat::make('Team / Staff', number_format(max($staff, 0)))
                ->description('Admin & internal roles')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),
            Stat::make('Customers', number_format($customers))
                ->description('Registered platform users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Open Jobs', number_format($jobs))
                ->description(number_format($applications) . ' applications')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),
            Stat::make('Talent Pool', number_format($seekers + $groups))
                ->description(number_format($seekers) . ' seekers · ' . number_format($groups) . ' groups')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning'),
        ];
    }
}
