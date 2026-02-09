<?php

namespace App\Filament\CustomerWidgets;

use App\Models\Listing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerListingsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        
        return [
            Stat::make('Total Listings', Listing::where('customer_id', $user->customer_id ?? 0)->count())
                ->description('All your listings')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Active Listings', Listing::where('customer_id', $user->customer_id ?? 0)->where('status', 'active')->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pending Approval', Listing::where('customer_id', $user->customer_id ?? 0)->where('approval_status', 'pending')->count())
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Expired Listings', Listing::where('customer_id', $user->customer_id ?? 0)->where('status', 'expired')->count())
                ->description('Need renewal')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
