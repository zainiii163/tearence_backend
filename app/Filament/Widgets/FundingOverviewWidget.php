<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\ProjectPromotion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FundingOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $totalFunding = Project::sum('current_funding');
        $totalGoals = Project::sum('funding_goal');
        $pendingReview = Project::where('status', 'draft')->whereNotNull('submitted_at')->count();
        $activePromotions = ProjectPromotion::where('status', 'active')->count();

        // Calculate average funding progress
        $avgProgress = $totalGoals > 0 ? ($totalFunding / $totalGoals) * 100 : 0;

        // Get recent projects count (last 30 days)
        $recentProjects = Project::where('created_at', '>=', now()->subDays(30))->count();

        // Get promotion revenue
        $promotionRevenue = ProjectPromotion::where('status', 'active')->sum('amount_paid');

        return [
            Stat::make('Total Projects', number_format($totalProjects))
                ->description($recentProjects . ' added in last 30 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),

            Stat::make('Active Campaigns', number_format($activeProjects))
                ->description($totalProjects > 0 ? round(($activeProjects / $totalProjects) * 100, 1) . '% of total' : '0%')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success'),

            Stat::make('Total Funding', '$' . number_format($totalFunding, 2))
                ->description('of $' . number_format($totalGoals, 2) . ' goal')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($avgProgress >= 100 ? 'success' : ($avgProgress >= 50 ? 'warning' : 'danger')),

            Stat::make('Pending Review', number_format($pendingReview))
                ->description('Need approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingReview > 0 ? 'warning' : 'success'),

            Stat::make('Active Promotions', number_format($activePromotions))
                ->description('Generating $' . number_format($promotionRevenue, 2))
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->color('info'),

            Stat::make('Avg Progress', round($avgProgress, 1) . '%')
                ->description('Funding completion')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgProgress >= 100 ? 'success' : ($avgProgress >= 50 ? 'warning' : 'danger')),
        ];
    }
}
