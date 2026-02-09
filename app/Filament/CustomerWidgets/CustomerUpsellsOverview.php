<?php

namespace App\Filament\CustomerWidgets;

use App\Models\ListingUpsell;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerUpsellsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $user = auth()->user();
        
        return [
            Stat::make('Active Upsells', ListingUpsell::where('customer_id', $user->customer_id ?? 0)->where('status', 'active')->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
            Stat::make('Total Spent', ListingUpsell::where('customer_id', $user->customer_id ?? 0)->where('payment_status', 'paid')->sum('price'))
                ->description('On upsells')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 2)),
            Stat::make('Pending Payments', ListingUpsell::where('customer_id', $user->customer_id ?? 0)->where('payment_status', 'pending')->count())
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
