<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Models\Vehicle;
use App\Models\VehicleAnalytic;
use App\Models\VehicleFavourite;
use App\Models\VehicleEnquiry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VehicleStatsWidget extends BaseWidget
{
    public Vehicle $record;

    protected function getStats(): array
    {
        $vehicle = $this->record;

        return [
            Stat::make('Total Views', $vehicle->views_count ?? 0)
                ->description('All time views')
                ->icon('heroicon-o-eye')
                ->color('primary'),
            
            Stat::make('Total Saves', $vehicle->saves_count ?? 0)
                ->description('Users who saved this vehicle')
                ->icon('heroicon-o-heart')
                ->color('success'),
            
            Stat::make('Total Enquiries', $vehicle->enquiries_count ?? 0)
                ->description('Contact requests received')
                ->icon('heroicon-o-envelope')
                ->color('warning'),
            
            Stat::make('Status', ucfirst($vehicle->status ?? 'unknown'))
                ->description('Current vehicle status')
                ->icon('heroicon-o-flag')
                ->color(fn (): string => match ($vehicle->status) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    'expired' => 'gray',
                    'sold' => 'info',
                    default => 'gray',
                }),
        ];
    }
}
