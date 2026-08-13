<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Resources\AffiliateHopConversionResource;
use App\Filament\Resources\AffiliatePayoutResource;
use App\Filament\Widgets\AffiliatePayoutsOverviewWidget;
use Filament\Actions\Action;
use Filament\Pages\Page;

class AffiliatePayouts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Affiliate Payouts';

    protected static ?string $title = 'Affiliate Payouts';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 8;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    protected function getHeaderWidgets(): array
    {
        return [
            AffiliatePayoutsOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('payout_requests')
                ->label('Manage payout requests')
                ->icon('heroicon-o-banknotes')
                ->url(AffiliatePayoutResource::getUrl('index')),
            Action::make('hop_conversions')
                ->label('Hop conversions')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->url(AffiliateHopConversionResource::getUrl('index')),
        ];
    }
}
