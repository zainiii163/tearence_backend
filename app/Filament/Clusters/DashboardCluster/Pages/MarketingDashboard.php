<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\MarketingDashboardStatsWidget;
use App\Filament\Widgets\MarketingMixChartWidget;
use Filament\Pages\Page;

class MarketingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Marketing';

    protected static ?string $title = 'Marketing Dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Paid placements, banners, affiliate pipeline, and campaign reach.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MarketingDashboardStatsWidget::class,
            MarketingMixChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
