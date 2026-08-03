<?php

namespace App\Filament\Widgets;

use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use App\Services\CrossPromotionFeedService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Clive: public promoted page has no counters — track them in Filament admin.
 */
class PromotedAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $total = PromotedAdvert::count();
        $active = method_exists(PromotedAdvert::class, 'scopeActive')
            ? PromotedAdvert::active()->count()
            : PromotedAdvert::where('is_active', true)->count();
        $views = (int) PromotedAdvert::sum('views_count');
        $countries = PromotedAdvert::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct('country')
            ->count('country');
        $categories = 0;
        try {
            $categories = PromotedAdvertCategory::query()->count();
        } catch (\Throwable $e) {
            // ignore
        }

        $siteFeedTotal = 0;
        try {
            $siteFeedTotal = app(CrossPromotionFeedService::class)->adminCount('promoted');
        } catch (\Throwable $e) {
            $siteFeedTotal = $total;
        }

        $revenue = (float) PromotedAdvert::sum('promotion_price');

        return [
            Stat::make('Dedicated promoted ads', number_format($total))
                ->description('In promoted_adverts table')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            Stat::make('Site-wide promoted feed', number_format($siteFeedTotal))
                ->description('Across marketplace categories')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),

            Stat::make('Active promotions', number_format($active))
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total views', number_format($views))
                ->description('Dedicated promoted views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Countries', number_format($countries))
                ->description('Distinct countries')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('gray'),

            Stat::make('Categories', number_format($categories))
                ->description('Promoted categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make('Promotion revenue', '£' . number_format($revenue, 2))
                ->description('From promotion fees')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('success'),
        ];
    }
}
