<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\AffiliatePayoutsOverviewWidget;
use Filament\Pages\Page;

class AffiliatePayouts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Affiliate Payouts';

    protected static ?string $title = 'Affiliate Payouts';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            AffiliatePayoutsOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
