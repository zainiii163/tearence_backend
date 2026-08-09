<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $buySellOrders = DashboardMetrics::count('buy_sell_purchases');
        $buySellPaid = DashboardMetrics::sum('buy_sell_purchases', 'price', fn ($q) => $q->where('payment_status', 'paid'));
        $templateOrders = DashboardMetrics::count('template_purchases');
        $templateRevenue = DashboardMetrics::sum('template_purchases', 'amount', fn ($q) => $q->whereIn('status', ['paid', 'completed', 'success']));
        if ($templateRevenue <= 0) {
            $templateRevenue = DashboardMetrics::sum('template_purchases', 'price');
        }
        $bookOrders = DashboardMetrics::count('book_purchases') + DashboardMetrics::count('book_advert_purchases');
        $imageOrders = DashboardMetrics::count('image_advert_purchases');
        $travelBookings = DashboardMetrics::count('travel_bookings');
        $serviceOrders = DashboardMetrics::count('service_orders');

        $gmv = $buySellPaid + $templateRevenue
            + DashboardMetrics::sum('book_purchases', 'amount')
            + DashboardMetrics::sum('book_advert_purchases', 'amount')
            + DashboardMetrics::sum('image_advert_purchases', 'amount')
            + DashboardMetrics::sum('banner_purchases', 'amount');

        return [
            Stat::make('Marketplace GMV', DashboardMetrics::money($gmv))
                ->description('Paid marketplace volume')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            Stat::make('Buy & Sell Orders', number_format($buySellOrders))
                ->description(DashboardMetrics::money($buySellPaid) . ' paid')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
            Stat::make('Digital Sales', number_format($templateOrders + $bookOrders + $imageOrders))
                ->description('Templates · books · stock media')
                ->descriptionIcon('heroicon-m-cloud-arrow-down')
                ->color('info'),
            Stat::make('Bookings & Services', number_format($travelBookings + $serviceOrders))
                ->description('Travel bookings + service orders')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),
        ];
    }
}
