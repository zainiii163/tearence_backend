<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Resources\FleetManagementResource;
use App\Filament\Resources\VehicleResource;
use App\Filament\Widgets\FleetDashboardStatsWidget;
use Filament\Actions\Action;
use Filament\Pages\Page;

/**
 * Super-admin entry to fleet ops from the Dashboards cluster.
 */
class FleetDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Fleet';

    protected static ?string $title = 'Fleet Management';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Operational status across all vehicle listings (available, on hire, maintenance, sold). Open the full board to update statuses.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openFleetBoard')
                ->label('Open fleet board')
                ->icon('heroicon-o-queue-list')
                ->url(FleetManagementResource::getUrl('index'))
                ->color('primary'),
            Action::make('openVehicles')
                ->label('All vehicles')
                ->icon('heroicon-o-truck')
                ->url(VehicleResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FleetDashboardStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
