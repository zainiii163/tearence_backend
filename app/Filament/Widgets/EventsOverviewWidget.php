<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Events', Event::count())
                ->description('All events in the system')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make('Active Events', Event::where('is_active', true)->count())
                ->description('Currently active events')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([12, 11, 14, 13, 15, 14, 16])
                ->color('success'),

            Stat::make('Upcoming Events', Event::where('date_time', '>=', now())->count())
                ->description('Events scheduled for the future')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([5, 7, 9, 8, 11, 10, 12])
                ->color('warning'),

            Stat::make('Featured Events', Event::whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])->count())
                ->description('Promoted events')
                ->descriptionIcon('heroicon-m-star')
                ->chart([2, 3, 4, 3, 5, 4, 6])
                ->color('info'),
        ];
    }
}
