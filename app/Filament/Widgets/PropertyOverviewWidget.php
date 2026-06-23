<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PropertyOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        return [
            Stat::make('Total Properties', Property::count())
                ->description('All properties in system')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
                
            Stat::make('Active Properties', Property::where('active', true)->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Pending Approval', Property::where('approved', false)->count())
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Total Views', Property::sum('views'))
                ->description('All-time views')
                ->descriptionIcon('heroicon-m-eye')
                ->chart([12, 11, 14, 13, 16, 15, 18])
                ->color('info'),
        ];
    }
}
