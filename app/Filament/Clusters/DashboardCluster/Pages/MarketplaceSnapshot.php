<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\BannerOverviewWidget;
use App\Filament\Widgets\EventsOverviewWidget;
use App\Filament\Widgets\PropertyOverviewWidget;
use App\Filament\Widgets\VenuesOverviewWidget;
use Filament\Pages\Page;

/**
 * Former long dashboard board content — Events / Property / Banners.
 */
class MarketplaceSnapshot extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Marketplace Snapshot';

    protected static ?string $title = 'Marketplace Snapshot';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            EventsOverviewWidget::class,
            VenuesOverviewWidget::class,
            PropertyOverviewWidget::class,
            BannerOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }
}
