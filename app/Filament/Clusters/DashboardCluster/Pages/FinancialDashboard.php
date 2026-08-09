<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\FinancialDashboardStatsWidget;
use App\Filament\Widgets\FinancialTrendChartWidget;
use Filament\Pages\Page;

class FinancialDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Financial';

    protected static ?string $title = 'Financial Dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Platform revenue, monthly performance, donations, funding pledges, and settlements.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialDashboardStatsWidget::class,
            FinancialTrendChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
