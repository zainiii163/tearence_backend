<?php

namespace App\Filament\Clusters\AdvertAnalyticsCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\SponsoredAdvertsOverviewWidget;
use App\Filament\Widgets\SponsoredByCountryWidget;
use Filament\Pages\Page;

class SponsoredAdvertsAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Sponsored Adverts Analytics';

    protected static ?string $title = 'Sponsored Adverts Analytics';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            SponsoredAdvertsOverviewWidget::class,
            SponsoredByCountryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
