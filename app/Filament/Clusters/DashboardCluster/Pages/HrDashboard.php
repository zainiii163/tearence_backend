<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\HrDashboardStatsWidget;
use App\Filament\Widgets\HrWorkforceChartWidget;
use Filament\Pages\Page;

class HrDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'HR';

    protected static ?string $title = 'HR Dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Staff, customers, job listings, applications, and talent pool.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HrDashboardStatsWidget::class,
            HrWorkforceChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
