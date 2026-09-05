<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\FleetManagementResource;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class FleetDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $fleetUrl = FleetManagementResource::getUrl('index');
        $total = Vehicle::query()->count();

        if (! Schema::hasColumn('vehicles', 'fleet_status')) {
            return [
                Stat::make('Fleet size', number_format($total))
                    ->description('Run migrate to enable fleet statuses')
                    ->descriptionIcon('heroicon-m-truck')
                    ->color('primary')
                    ->url($fleetUrl),
            ];
        }

        $available = Vehicle::query()->where('fleet_status', 'available')->count();
        $inService = Vehicle::query()->where('fleet_status', 'in_service')->count();
        $maintenance = Vehicle::query()->where('fleet_status', 'maintenance')->count();
        $sold = Vehicle::query()->where('fleet_status', 'sold')->count();

        return [
            Stat::make('Fleet size', number_format($total))
                ->description('All vehicle listings')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->url($fleetUrl),
            Stat::make('Available', number_format($available))
                ->description('Ready for sale / hire')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url($fleetUrl.'?tableFilters[fleet_status][value]=available'),
            Stat::make('In service', number_format($inService))
                ->description('On hire / assigned')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->url($fleetUrl.'?tableFilters[fleet_status][value]=in_service'),
            Stat::make('Maintenance', number_format($maintenance))
                ->description(number_format($sold).' marked sold')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->url($fleetUrl.'?tableFilters[fleet_status][value]=maintenance'),
        ];
    }
}
