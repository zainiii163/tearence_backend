<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class HrWorkforceChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Workforce snapshot';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
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

        return [
            'datasets' => [
                [
                    'label' => 'Count',
                    'data' => [$staff, $customers, $jobs, $applications, $seekers],
                    'backgroundColor' => [
                        '#d97706',
                        '#3b82f6',
                        '#059669',
                        '#8b5cf6',
                        '#64748b',
                    ],
                ],
            ],
            'labels' => ['Staff', 'Customers', 'Jobs', 'Applications', 'Seekers'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
