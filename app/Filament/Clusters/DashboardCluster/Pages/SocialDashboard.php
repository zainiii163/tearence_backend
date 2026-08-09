<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\SocialActivityChartWidget;
use App\Filament\Widgets\SocialDashboardStatsWidget;
use Filament\Pages\Page;

class SocialDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Social';

    protected static ?string $title = 'Social Dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Conversations, chat volume, community posts, contact inbox, and reports.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SocialDashboardStatsWidget::class,
            SocialActivityChartWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
