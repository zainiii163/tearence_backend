<?php

namespace App\Filament\Widgets;

use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromotedAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Promoted Adverts', PromotedAdvert::count())
                ->description('All promoted listings')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            
            Stat::make('Active Promotions', PromotedAdvert::active()->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 3, 8, 6, 12, 8, 15]),
            
            Stat::make('Total Revenue', '£' . number_format(PromotedAdvert::sum('promotion_price'), 2))
                ->description('From promotions')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('warning'),
            
            Stat::make('Categories', PromotedAdvertCategory::active()->count())
                ->description('Active categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
        ];
    }
}
