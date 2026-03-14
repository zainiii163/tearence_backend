<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleFavourite;
use App\Models\VehicleEnquiry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class VehicleOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('is_active', true)->count();
        $inactiveVehicles = Vehicle::where('is_active', false)->count();
        
        // Since analytics columns don't exist yet, use related tables or set to 0
        $totalViews = 0; // Vehicle::sum('views') ?? 0;
        $totalSaves = VehicleFavourite::count() ?? 0; // Use favourites table instead
        $totalEnquiries = VehicleEnquiry::count() ?? 0; // Use enquiries table instead

        return [
            Stat::make('Total Vehicles', $totalVehicles)
                ->description('All vehicle adverts')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),
            
            Stat::make('Active Vehicles', $activeVehicles)
                ->description('Currently live')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Inactive Vehicles', $inactiveVehicles)
                ->description('Not active')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
            
            Stat::make('Total Views', $totalViews)
                ->description('Analytics not yet implemented')
                ->icon('heroicon-o-eye')
                ->color('info'),
            
            Stat::make('Total Saves', $totalSaves)
                ->description('Users saved vehicles')
                ->icon('heroicon-o-heart')
                ->color('danger'),
            
            Stat::make('Total Enquiries', $totalEnquiries)
                ->description('Contact requests')
                ->icon('heroicon-o-envelope')
                ->color('warning'),
        ];
    }
}
