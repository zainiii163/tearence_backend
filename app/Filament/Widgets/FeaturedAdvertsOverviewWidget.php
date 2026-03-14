<?php

namespace App\Filament\Widgets;

use App\Models\FeaturedAdvert;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FeaturedAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalActive = FeaturedAdvert::active()->count();
        $totalPending = FeaturedAdvert::where('payment_status', 'pending')->count();
        $totalPaid = FeaturedAdvert::where('payment_status', 'paid')->count();
        $totalRevenue = FeaturedAdvert::where('payment_status', 'paid')->sum('upsell_price');

        $promotedCount = FeaturedAdvert::active()->promoted()->count();
        $featuredCount = FeaturedAdvert::active()->featured()->count();
        $sponsoredCount = FeaturedAdvert::active()->sponsored()->count();

        $totalViews = FeaturedAdvert::active()->sum('view_count');
        $totalSaves = FeaturedAdvert::active()->sum('save_count');
        $totalContacts = FeaturedAdvert::active()->sum('contact_count');

        return [
            Stat::make('Total Active', $totalActive)
                ->description('Currently active featured adverts')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),

            Stat::make('Pending Payment', $totalPending)
                ->description('Awaiting payment confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Revenue', '£' . number_format($totalRevenue, 2))
                ->description('From paid featured adverts')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Views', number_format($totalViews))
                ->description('Total views on all active adverts')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Promoted', $promotedCount)
                ->description('Promoted tier adverts')
                ->descriptionIcon('heroicon-m-arrow-up')
                ->color('warning'),

            Stat::make('Featured', $featuredCount)
                ->description('Featured tier adverts')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make('Sponsored', $sponsoredCount)
                ->description('Sponsored tier adverts')
                ->descriptionIcon('heroicon-m-crown')
                ->color('danger'),

            Stat::make('Total Saves', number_format($totalSaves))
                ->description('Total saves on all active adverts')
                ->descriptionIcon('heroicon-m-heart')
                ->color('pink'),
        ];
    }
}
