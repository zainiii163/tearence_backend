<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\SalesDashboardStatsWidget;
use App\Filament\Widgets\SalesTrendChartWidget;
use Filament\Pages\Page;

class SalesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $title = 'Sales Dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Marketplace orders, digital product sales, bookings, and service revenue.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesDashboardStatsWidget::class,
            SalesTrendChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
