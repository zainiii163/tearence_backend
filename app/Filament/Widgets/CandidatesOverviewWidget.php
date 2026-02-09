<?php

namespace App\Filament\Widgets;

use App\Models\CandidateProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CandidatesOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCandidates = CandidateProfile::count();
        $activeCandidates = CandidateProfile::where('visibility', 'public')->count();
        $featuredCandidates = CandidateProfile::where('is_featured', true)
            ->where(function($query) {
                $query->whereNull('featured_expires_at')
                      ->orWhere('featured_expires_at', '>=', now());
            })
            ->count();
        $boostedCandidates = CandidateProfile::where('has_job_alerts_boost', true)
            ->where(function($query) {
                $query->whereNull('job_alerts_boost_expires_at')
                      ->orWhere('job_alerts_boost_expires_at', '>=', now());
            })
            ->count();

        return [
            Stat::make('Total Candidates', $totalCandidates)
                ->description('All profiles')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([5, 10, 15, 20, 25, $totalCandidates]),
            Stat::make('Active Profiles', $activeCandidates)
                ->description('Public visibility')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->chart([3, 7, 10, 12, 15, $activeCandidates]),
            Stat::make('Featured Profiles', $featuredCandidates)
                ->description('Premium candidates')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->chart([1, 2, 3, 4, 5, $featuredCandidates]),
            Stat::make('Boosted Alerts', $boostedCandidates)
                ->description('Job alerts boost active')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info')
                ->chart([1, 1, 2, 2, 3, $boostedCandidates]),
        ];
    }
}

