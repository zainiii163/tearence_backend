<?php

namespace App\Filament\Widgets;

use App\Models\SponsoredAdvert;
use App\Models\SponsoredAdvertInquiry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SponsoredAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalAdverts = SponsoredAdvert::count();
        $pendingApproval = SponsoredAdvert::where('status', 'pending')->count();
        $activeAdverts = SponsoredAdvert::where('status', 'approved')->where('is_active', true)->count();
        $pendingInquiries = SponsoredAdvertInquiry::where('status', 'pending')->count();
        
        // Revenue this month
        $revenueThisMonth = SponsoredAdvert::where('payment_status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('tier_price');

        return [
            Stat::make('Total Sponsored Adverts', $totalAdverts)
                ->description('All time adverts')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, 20]),
            
            Stat::make('Pending Approval', $pendingApproval)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([2, 3, 5, 4, 3, 2, $pendingApproval]),
            
            Stat::make('Active Adverts', $activeAdverts)
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([10, 12, 15, 18, 20, 22, $activeAdverts]),
            
            Stat::make('Pending Inquiries', $pendingInquiries)
                ->description('Customer inquiries')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info')
                ->chart([3, 2, 4, 3, 5, 4, $pendingInquiries]),
            
            Stat::make('Revenue This Month', '£' . number_format($revenueThisMonth, 2))
                ->description('Monthly revenue')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('success')
                ->chart([500, 800, 1200, 1500, 1800, 2100, $revenueThisMonth]),
        ];
    }
}
