<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\CrossSitePromotionFeedWidget;
use App\Filament\Widgets\FeaturedAdvertsChartWidget;
use Filament\Pages\Page;

class AnalyticsOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'Analytics';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            CrossSitePromotionFeedWidget::class,
            FeaturedAdvertsChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
