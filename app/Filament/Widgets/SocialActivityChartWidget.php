<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class SocialActivityChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Social activity (14 days)';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $messages = DashboardMetrics::dailyCountSeries('chat_messages', 'created_at', 14);
        $posts = DashboardMetrics::dailyCountSeries('community_posts', 'created_at', 14);

        return [
            'datasets' => [
                [
                    'label' => 'Messages',
                    'data' => $messages['values'],
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Community posts',
                    'data' => $posts['values'],
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $messages['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
