<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\DashboardCluster\Pages\AnalyticsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Home entry kept for Filament routing; navigation uses DashboardCluster drawer instead.
 */
class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Admin Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string $panel = 'admin';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->redirect(AnalyticsOverview::getUrl());
    }
}
