<?php

namespace App\Filament\Widgets;

use App\Models\SponsoredAdvert;
use App\Models\SponsoredAdvertInquiry;
use App\Services\CrossPromotionFeedService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

/**
 * Clive: public sponsored page has no counters — track them here in Filament admin.
 */
class SponsoredAdvertsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalAdverts = SponsoredAdvert::count();
        $pendingApproval = SponsoredAdvert::where('status', 'pending')->count();
        $activeAdverts = SponsoredAdvert::query()
            ->where(function ($q) {
                $q->where('status', 'approved')->orWhereNull('status');
            })
            ->where('is_active', true)
            ->count();

        $totalViews = (int) SponsoredAdvert::sum('views_count');
        $countries = SponsoredAdvert::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct('country')
            ->count('country');

        $pendingInquiries = 0;
        try {
            $pendingInquiries = SponsoredAdvertInquiry::where('status', 'pending')->count();
        } catch (\Throwable $e) {
            // table may not exist in all environments
        }

        $revenueThisMonth = (float) SponsoredAdvert::where('payment_status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum(DB::raw('COALESCE(tier_price, sponsorship_price, 0)'));

        $siteFeedTotal = 0;
        try {
            $siteFeedTotal = app(CrossPromotionFeedService::class)->adminCount('sponsored');
        } catch (\Throwable $e) {
            $siteFeedTotal = $totalAdverts;
        }

        return [
            Stat::make('Dedicated sponsored ads', number_format($totalAdverts))
                ->description('In sponsored_adverts table')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),

            Stat::make('Site-wide sponsored feed', number_format($siteFeedTotal))
                ->description('Across categories (vehicles, property, etc.)')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),

            Stat::make('Active sponsored', number_format($activeAdverts))
                ->description('Currently running')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total views', number_format($totalViews))
                ->description('Dedicated sponsored views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('Countries', number_format($countries))
                ->description('Distinct countries with sponsored ads')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('gray'),

            Stat::make('Pending approval', number_format($pendingApproval))
                ->description($pendingInquiries ? "{$pendingInquiries} pending inquiries" : 'Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Revenue this month', '£' . number_format($revenueThisMonth, 2))
                ->description('Paid sponsorships')
                ->descriptionIcon('heroicon-m-currency-pound')
                ->color('success'),
        ];
    }
}
