<?php

namespace App\Filament\Widgets;

use App\Models\JobUpsell;
use App\Models\CandidateUpsell;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UpsellsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeJobUpsells = JobUpsell::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->count();
        
        $activeCandidateUpsells = CandidateUpsell::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->count();
        
        $pendingJobUpsells = JobUpsell::where('status', 'pending')->count();
        $pendingCandidateUpsells = CandidateUpsell::where('status', 'pending')->count();

        return [
            Stat::make('Active Job Upsells', $activeJobUpsells)
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-star')
                ->color('success')
                ->chart([2, 3, 4, 5, 6, $activeJobUpsells]),
            Stat::make('Active Candidate Upsells', $activeCandidateUpsells)
                ->description('Profile boosts active')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info')
                ->chart([1, 2, 2, 3, 3, $activeCandidateUpsells]),
            Stat::make('Pending Job Upsells', $pendingJobUpsells)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([1, 1, 2, 2, 2, $pendingJobUpsells]),
            Stat::make('Pending Candidate Upsells', $pendingCandidateUpsells)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([0, 1, 1, 1, 1, $pendingCandidateUpsells]),
        ];
    }
}

