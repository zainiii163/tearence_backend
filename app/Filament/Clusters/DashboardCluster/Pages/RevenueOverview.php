<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\RevenueOverviewWidget;
use Filament\Pages\Page;

class RevenueOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Revenue';

    protected static ?string $title = 'Revenue';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            RevenueOverviewWidget::class,
            RevenueChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
