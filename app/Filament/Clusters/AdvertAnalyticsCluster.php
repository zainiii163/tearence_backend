<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

/**
 * Replaced by DashboardCluster — kept so old class references do not fatal.
 */
class AdvertAnalyticsCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Advert Analytics';

    protected static ?string $clusterBreadcrumb = 'Advert Analytics';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
