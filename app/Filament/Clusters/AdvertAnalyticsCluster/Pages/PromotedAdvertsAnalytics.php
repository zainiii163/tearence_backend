<?php

namespace App\Filament\Clusters\AdvertAnalyticsCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\PromotedAdvertsOverviewWidget;
use App\Filament\Widgets\PromotedByCountryWidget;
use Filament\Pages\Page;

class PromotedAdvertsAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Promoted Adverts Analytics';

    protected static ?string $title = 'Promoted Adverts Analytics';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            PromotedAdvertsOverviewWidget::class,
            PromotedByCountryWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
