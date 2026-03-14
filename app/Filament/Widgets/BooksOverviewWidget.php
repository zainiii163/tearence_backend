<?php

namespace App\Filament\Widgets;

use App\Models\BookAdvert;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BooksOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Books', BookAdvert::count())
                ->description('Total number of book adverts')
                ->descriptionIcon('heroicon-m-book-open')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),

            Stat::make('Active Books', BookAdvert::where('status', 'active')->count())
                ->description('Currently active books')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total Authors', BookAdvert::distinct('user_id')->count('user_id'))
                ->description('Number of authors')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),

            Stat::make('Total Views', number_format(BookAdvert::sum('views_count')))
                ->description('Total views across all books')
                ->descriptionIcon('heroicon-m-eye')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
        ];
    }
}
