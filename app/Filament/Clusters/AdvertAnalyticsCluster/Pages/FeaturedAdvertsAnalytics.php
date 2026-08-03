<?php

namespace App\Filament\Clusters\AdvertAnalyticsCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\FeaturedAdvertsChartWidget;
use App\Filament\Widgets\FeaturedAdvertsOverviewWidget;
use App\Filament\Widgets\FeaturedByCountryWidget;
use Filament\Pages\Page;

class FeaturedAdvertsAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Featured Adverts Analytics';

    protected static ?string $title = 'Featured Adverts Analytics';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            FeaturedAdvertsOverviewWidget::class,
            FeaturedByCountryWidget::class,
            FeaturedAdvertsChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
