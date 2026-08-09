<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

/**
 * Clive: split the long admin board into a Dashboard drawer with sub-topics.
 */
class DashboardCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboards';

    protected static ?string $clusterBreadcrumb = 'Dashboards';

    protected static ?int $navigationSort = -10;
}
