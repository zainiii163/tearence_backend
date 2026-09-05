<?php

namespace App\Filament\Resources\FleetManagementResource\Pages;

use App\Filament\Resources\FleetManagementResource;
use App\Filament\Resources\VehicleResource;
use App\Filament\Widgets\FleetDashboardStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFleetManagement extends ListRecords
{
    protected static string $resource = FleetManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('allVehicles')
                ->label('All vehicle listings')
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
}
