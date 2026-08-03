<?php

namespace App\Filament\Widgets;

use App\Models\FeaturedAdvert;
use App\Services\CrossPromotionFeedService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Clive: public featured page has no counters — track them in Filament admin.
 */
class FeaturedAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalActive = FeaturedAdvert::query()->when(
            method_exists(FeaturedAdvert::class, 'scopeActive'),
            fn ($q) => $q->active(),
            fn ($q) => $q->where('is_active', true)
        )->count();

        $totalPending = FeaturedAdvert::where('payment_status', 'pending')->count();
        $totalRevenue = (float) FeaturedAdvert::where('payment_status', 'paid')->sum('upsell_price');
        $totalViews = (int) FeaturedAdvert::sum('view_count');
        $totalSaves = (int) FeaturedAdvert::sum('save_count');
        $countries = FeaturedAdvert::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct('country')
            ->count('country');

        $siteFeedTotal = 0;
        try {
            $siteFeedTotal = app(CrossPromotionFeedService::class)->adminCount('featured');
        } catch (\Throwable $e) {
            $siteFeedTotal = $totalActive;
        }

        return [
            Stat::make('Active featured ads', number_format($totalActive))
                ->description('Dedicated featured_adverts table')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            Stat::make('Site-wide featured feed', number_format($siteFeedTotal))
                ->description('Across marketplace categories')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),

            Stat::make('Pending payment', number_format($totalPending))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total views', number_format($totalViews))
                ->description('Dedicated featured views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Countries', number_format($countries))
                ->description('Distinct countries')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('gray'),

            Stat::make('Total saves', number_format($totalSaves))
                ->description('User saves')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),

            Stat::make('Revenue', '£' . number_format($totalRevenue, 2))
                ->description('From paid featured ads')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
