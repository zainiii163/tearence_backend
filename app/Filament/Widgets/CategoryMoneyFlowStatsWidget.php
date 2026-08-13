<?php

namespace App\Filament\Widgets;

use App\Services\CategoryMoneyFlowService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoryMoneyFlowStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '120s';

    protected function getStats(): array
    {
        $summary = app(CategoryMoneyFlowService::class)->summarize();
        $t = $summary['totals'];

        return [
            Stat::make('Our money', '$'.number_format($t['our_money'], 2))
                ->description('Products, fees, adverts & commissions')
                ->color('success'),
            Stat::make('Seller payouts', '$'.number_format($t['seller_payouts'], 2))
                ->description('Owed / paid to sellers & affiliates')
                ->color('warning'),
            Stat::make('Other monies', '$'.number_format($t['other_monies'], 2))
                ->description('Donations, funding, pass-through')
                ->color('info'),
            Stat::make('Gross recorded', '$'.number_format($t['gross'], 2))
                ->description($t['transactions'].' ledger rows')
                ->color('primary'),
        ];
    }
}
