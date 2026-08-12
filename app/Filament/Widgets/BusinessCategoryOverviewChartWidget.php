<?php

namespace App\Filament\Widgets;

use App\Models\AffiliateApplication;
use App\Models\BusinessAffiliateOffer;
use App\Models\CustomerBusiness;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class BusinessCategoryOverviewChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Business accounts & affiliate pipeline';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '120s';

    protected function getData(): array
    {
        $labels = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->format('M j'))->values()->all();

        $businessData = collect(range(6, 0))->map(function ($daysAgo) {
            if (!Schema::hasTable('customer_business')) {
                return 0;
            }
            $date = now()->subDays($daysAgo)->toDateString();

            return CustomerBusiness::whereDate('created_at', $date)->count();
        })->values()->all();

        $offerData = collect(range(6, 0))->map(function ($daysAgo) {
            if (!Schema::hasTable('business_affiliate_offers')) {
                return 0;
            }
            $date = now()->subDays($daysAgo)->toDateString();

            return BusinessAffiliateOffer::whereDate('created_at', $date)->count();
        })->values()->all();

        $appData = collect(range(6, 0))->map(function ($daysAgo) {
            if (!Schema::hasTable('affiliate_applications')) {
                return 0;
            }
            $date = now()->subDays($daysAgo)->toDateString();

            return AffiliateApplication::whereDate('created_at', $date)->count();
        })->values()->all();

        return [
            'datasets' => [
                [
                    'label' => 'New businesses',
                    'data' => $businessData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Affiliate offers',
                    'data' => $offerData,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Promoter applications',
                    'data' => $appData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
