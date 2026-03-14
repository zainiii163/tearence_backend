<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeeker;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalJobs = Job::count();
        $activeJobs = Job::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();
        $totalApplications = JobApplication::count();
        $totalSeekers = JobSeeker::active()->count();

        return [
            Stat::make('Total Jobs', $totalJobs)
                ->description('All job postings')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary')
                ->chart([7, 12, 15, 18, 20, $totalJobs]),
            Stat::make('Active Jobs', $activeJobs)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 8, 10, 12, 15, $activeJobs]),
            Stat::make('Total Applications', $totalApplications)
                ->description('Job applications')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart([10, 15, 20, 25, 30, $totalApplications]),
            Stat::make('Active Seekers', $totalSeekers)
                ->description('Job seekers')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning')
                ->chart([3, 5, 7, 9, 11, $totalSeekers]),
        ];
    }
}

