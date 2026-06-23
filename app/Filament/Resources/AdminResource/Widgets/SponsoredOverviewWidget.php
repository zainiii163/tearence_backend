<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\SponsoredAdvert;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SponsoredOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = DB::table('sponsored_adverts')
            ->selectRaw('
                COUNT(*) as total_adverts,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_adverts,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_adverts,
                SUM(views_count) as total_views,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_count,
                SUM(CASE WHEN sponsorship_tier = "featured" THEN 1 ELSE 0 END) as promoted_count,
                SUM(CASE WHEN sponsorship_tier = "sponsored" THEN 1 ELSE 0 END) as sponsored_count
            ')
            ->first();

        return [
            Stat::make('Total Adverts', $stats->total_adverts ?? 0)
                ->description('All sponsored adverts')
                ->icon('heroicon-o-document-text')
                ->color('primary'),
                
            Stat::make('Active', $stats->active_adverts ?? 0)
                ->description('Currently active')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
                
            Stat::make('Inactive', $stats->inactive_adverts ?? 0)
                ->description('Not active')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
                
            Stat::make('Total Views', number_format($stats->total_views ?? 0))
                ->description('All time views')
                ->icon('heroicon-o-eye')
                ->color('info'),
                
            Stat::make('Avg Rating', number_format($stats->avg_rating ?? 0, 2))
                ->description('Out of 5 stars')
                ->icon('heroicon-o-star')
                ->color('warning'),
                
            Stat::make('Featured', $stats->featured_count ?? 0)
                ->description('Featured adverts')
                ->icon('heroicon-o-sparkles')
                ->color('warning'),
        ];
    }
}
