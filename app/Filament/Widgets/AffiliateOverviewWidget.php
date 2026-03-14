<?php

namespace App\Filament\Widgets;

use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use App\Models\AffiliateCategory;
use App\Models\AffiliateApplication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AffiliateOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Business Offers', BusinessAffiliateOffer::count())
                ->description('Total business affiliate offers')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('User Posts', UserAffiliatePost::count())
                ->description('Total user affiliate posts')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([3, 5, 8, 12, 7, 15, 10]),

            Stat::make('Active Categories', AffiliateCategory::where('is_active', true)->count())
                ->description('Active affiliate categories')
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make('Pending Applications', AffiliateApplication::where('status', 'pending')->count())
                ->description('Applications awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
