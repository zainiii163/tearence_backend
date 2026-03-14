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
            \App\Filament\Widgets\VehicleOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\VehicleStatsChart::class,
            \App\Filament\Widgets\RecentVehiclesWidget::class,
        ];
    }
}

