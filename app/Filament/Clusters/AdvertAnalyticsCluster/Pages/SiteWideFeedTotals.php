<?php

namespace App\Filament\Clusters\AdvertAnalyticsCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\CrossSitePromotionFeedWidget;
use Filament\Pages\Page;

class SiteWideFeedTotals extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Site-wide feed totals';

    protected static ?string $title = 'Site-wide feed totals';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            CrossSitePromotionFeedWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
