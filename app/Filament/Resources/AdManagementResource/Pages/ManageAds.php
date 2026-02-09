<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use App\Filament\Resources\AdManagementResource\Widgets\AdStatsOverview;
use App\Filament\Resources\AdManagementResource\Widgets\AdPerformanceChart;
use App\Filament\Resources\AdManagementResource\Widgets\AdTypeDistribution;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ManageAds extends ListRecords
{
    protected static string $resource = AdManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analytics')
                ->label('Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(AdManagementResource::getUrl('analytics')),
            
            Action::make('reports')
                ->label('Reports')
                ->icon('heroicon-o-document-text')
                ->url(AdManagementResource::getUrl('reports')),
            
            Actions\CreateAction::make()
                ->label('Create Ad')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.ad-management.pages.header', [
            'stats' => [
                'total_ads' => \App\Models\Advertisement::count(),
                'active_ads' => \App\Models\Advertisement::where('is_active', true)->count(),
                'expired_ads' => \App\Models\Advertisement::where('end_date', '<', now())->count(),
            ]
        ]);
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->latest();
    }

    protected function getFooterWidgets(): array
    {
        return [
            AdStatsOverview::class,
            AdPerformanceChart::class,
            AdTypeDistribution::class,
        ];
    }
}
