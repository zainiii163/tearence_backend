<?php

namespace App\Filament\Resources\SponsoredAdvertResource\Pages;

use App\Filament\Resources\SponsoredAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;

class SponsoredAdvertAnalytics extends Page
{
    protected static string $resource = SponsoredAdvertResource::class;

    protected static string $view = 'filament.resources.sponsored-advert-resource.pages.sponsored-advert-analytics';

    public $record;

    public function mount($record): void
    {
        $this->record = $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Refresh Analytics')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->notify('success', 'Analytics refreshed successfully');
                }),
        ];
    }

    public function getAnalyticsData(): array
    {
        $advert = $this->record;
        
        // Basic stats
        $stats = [
            'views' => $advert->views_count,
            'clicks' => $advert->clicks_count,
            'saves' => $advert->saves_count,
            'inquiries' => $advert->inquiries_count,
        ];

        // Analytics events over time (last 30 days)
        $last30Days = now()->subDays(30);
        $analyticsData = \App\Models\SponsoredAdvertAnalytic::where('sponsored_advert_id', $advert->id)
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('event_type, DATE(created_at) as date, COUNT(*) as count')
            ->groupBy(['event_type', 'date'])
            ->orderBy('date')
            ->get()
            ->groupBy('event_type');

        // Geographic distribution
        $geoStats = \App\Models\SponsoredAdvertAnalytic::where('sponsored_advert_id', $advert->id)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Recent inquiries
        $recentInquiries = \App\Models\SponsoredAdvertInquiry::where('sponsored_advert_id', $advert->id)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        // Daily performance
        $dailyPerformance = \App\Models\SponsoredAdvertAnalytic::where('sponsored_advert_id', $advert->id)
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('DATE(created_at) as date, 
                        SUM(CASE WHEN event_type = "view" THEN 1 ELSE 0 END) as views,
                        SUM(CASE WHEN event_type = "click" THEN 1 ELSE 0 END) as clicks,
                        SUM(CASE WHEN event_type = "save" THEN 1 ELSE 0 END) as saves')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'stats' => $stats,
            'analytics_data' => $analyticsData,
            'geo_stats' => $geoStats,
            'recent_inquiries' => $recentInquiries,
            'daily_performance' => $dailyPerformance,
        ];
    }

    public function getViewData(): array
    {
        return array_merge($this->getAnalyticsData(), [
            'record' => $this->record,
        ]);
    }
}
