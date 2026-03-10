<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePromotion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ServicesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Services', Service::count())
                ->description('All services in the system')
                ->descriptionIcon('heroicon-m-briefcase')
                ->chart([7, 12, 10, 14, 15, 18, 20])
                ->color('primary'),
            
            Stat::make('Active Services', Service::where('status', 'active')->count())
                ->description('Currently active services')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([5, 8, 6, 10, 12, 14, 16])
                ->color('success'),
            
            Stat::make('Pending Services', Service::where('status', 'pending')->count())
                ->description('Services awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([2, 3, 1, 4, 2, 3, 1])
                ->color('warning'),
            
            Stat::make('Promoted Services', Service::whereNotNull('promotion_type')->count())
                ->description('Services with active promotions')
                ->descriptionIcon('heroicon-m-star')
                ->chart([1, 2, 3, 2, 4, 3, 5])
                ->color('info'),
        ];
    }
}
