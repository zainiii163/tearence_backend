<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SocialDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $conversations = DashboardMetrics::count('conversations');
        $messages = DashboardMetrics::count('chat_messages');
        $community = DashboardMetrics::count('community_posts');
        $contacts = DashboardMetrics::count('contact_messages')
            ?: DashboardMetrics::count('contact_inquiries')
            ?: DashboardMetrics::count('support_tickets');
        $comments = DashboardMetrics::count('community_comments')
            ?: DashboardMetrics::count('comments');
        $reports = DashboardMetrics::count('content_reports')
            ?: DashboardMetrics::count('reports');

        return [
            Stat::make('Conversations', number_format($conversations))
                ->description('Buyer–seller chat threads')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),
            Stat::make('Messages', number_format($messages))
                ->description('Total chat volume')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('info'),
            Stat::make('Community Posts', number_format($community))
                ->description(number_format($comments) . ' comments')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Inbox & Reports', number_format($contacts + $reports))
                ->description(number_format($contacts) . ' contacts · ' . number_format($reports) . ' reports')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('warning'),
        ];
    }
}
