<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ModerationStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pendingAds = Listing::where('approval_status', 'pending')->count();
        $harmfulAds = Listing::where('is_harmful', true)->count();
        $oldAds = Listing::where('created_at', '<', now()->subDays(21))->count();
        $totalAds = Listing::count();

        $pendingKyc = User::where('kyc_status', 'pending')->orWhere('kyc_status', 'submitted')->count();
        $verifiedKyc = User::where('kyc_status', 'verified')->count();
        $rejectedKyc = User::where('kyc_status', 'rejected')->count();
        $totalUsers = User::count();

        $approvedToday = Listing::where('approval_status', 'approved')
            ->whereDate('approved_at', today())
            ->count();

        $rejectedToday = Listing::where('approval_status', 'rejected')
            ->whereDate('updated_at', today())
            ->count();

        return [
            Stat::make('Pending Ads', $pendingAds)
                ->description('Ads awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Harmful Content', $harmfulAds)
                ->description('Flagged as harmful')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('danger'),

            Stat::make('Old Ads', $oldAds)
                ->description('21+ days old')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),

            Stat::make('Total Ads', $totalAds)
                ->description('All listings')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Pending KYC', $pendingKyc)
                ->description('Users awaiting verification')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),

            Stat::make('Verified KYC', $verifiedKyc)
                ->description('Verified users')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Approved Today', $approvedToday)
                ->description('Ads approved today')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),

            Stat::make('Rejected Today', $rejectedToday)
                ->description('Ads rejected today')
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),
        ];
    }
}
