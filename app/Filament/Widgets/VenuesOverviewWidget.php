<?php

namespace App\Filament\Widgets;

use App\Models\Venue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VenuesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Venues', Venue::count())
                ->description('All venues in the system')
                ->descriptionIcon('heroicon-m-building-office')
                ->chart([3, 5, 7, 4, 8, 6, 9])
                ->color('primary'),

            Stat::make('Active Venues', Venue::where('is_active', true)->count())
                ->description('Currently active venues')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([8, 9, 10, 9, 11, 10, 12])
                ->color('success'),

            Stat::make('Featured Venues', Venue::whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])->count())
                ->description('Promoted venues')
                ->descriptionIcon('heroicon-m-star')
                ->chart([1, 2, 3, 2, 4, 3, 5])
                ->color('warning'),

            Stat::make('Avg Capacity', number_format(Venue::avg('capacity')))
                ->description('Average venue capacity')
                ->descriptionIcon('heroicon-m-users')
                ->chart([50, 75, 100, 125, 150, 175, 200])
                ->color('info'),
        ];
    }
}
