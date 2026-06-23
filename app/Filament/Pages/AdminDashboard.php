<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\WidgetConfiguration;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Admin Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string $panel = 'admin';

    protected function getHeaderWidgets(): array
    {
        return [
            // Temporarily disabled to prevent timeout issues
            // \App\Filament\Widgets\VehicleOverviewWidget::class,
            // \App\Filament\Widgets\AffiliateOverviewWidget::class,
            // \App\Filament\Resources\AdminResource\Widgets\SponsoredOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Temporarily disabled to prevent timeout issues
            // \App\Filament\Widgets\AffiliateStatsChart::class,
            // \App\Filament\Widgets\RecentAffiliateContent::class,
            // \App\Filament\Resources\AdminResource\Widgets\RecentSponsoredAdvertsWidget::class,
            // \App\Filament\Resources\AdminResource\Widgets\SponsoredStatsChartWidget::class,
        ];
    }
}

