<?php

namespace App\Filament\Widgets;

use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use App\Models\AffiliateApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AffiliateStatsChart extends ChartWidget
{
    protected static ?string $heading = 'Affiliate Statistics (Last 7 Days)';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->format('Y-m-d');
        });

        // Business offers created per day
        $businessOffersData = $last7Days->map(function ($date) {
            return BusinessAffiliateOffer::whereDate('created_at', $date)->count();
        })->toArray();

        // User posts created per day
        $userPostsData = $last7Days->map(function ($date) {
            return UserAffiliatePost::whereDate('created_at', $date)->count();
        })->toArray();

        // Applications submitted per day
        $applicationsData = $last7Days->map(function ($date) {
            return AffiliateApplication::whereDate('created_at', $date)->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Business Offers',
                    'data' => $businessOffersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'User Posts',
                    'data' => $userPostsData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Applications',
                    'data' => $applicationsData,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.5)',
                    'borderColor' => 'rgba(251, 146, 60, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $last7Days->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('M j');
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
