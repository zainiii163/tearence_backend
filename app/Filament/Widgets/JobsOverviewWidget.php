<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalJobs = Listing::count();
        $activeJobs = Listing::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->count();
        $featuredJobs = Listing::where('is_featured', true)
            ->where(function($query) {
                $query->whereNull('featured_expires_at')
                      ->orWhere('featured_expires_at', '>=', now());
            })
            ->count();
        $expiredJobs = Listing::where('status', 'expired')
            ->orWhere(function($query) {
                $query->whereNotNull('end_date')
                      ->where('end_date', '<', now());
            })
            ->count();

        return [
            Stat::make('Total Jobs', $totalJobs)
                ->description('All job postings')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary')
                ->chart([7, 12, 15, 18, 20, $totalJobs]),
            Stat::make('Active Jobs', $activeJobs)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 8, 10, 12, 15, $activeJobs]),
            Stat::make('Featured Jobs', $featuredJobs)
                ->description('Premium listings')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->chart([2, 3, 4, 5, 6, $featuredJobs]),
            Stat::make('Expired Jobs', $expiredJobs)
                ->description('Need attention')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart([1, 2, 2, 3, 3, $expiredJobs]),
        ];
    }
}

