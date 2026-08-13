<?php

namespace App\Filament\Widgets;

use App\Models\CategoryMoneyFlow;
use App\Support\MarketplaceCategoryMap;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategoryMoneyFlowTableWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Money flow by category';

    public function table(Table $table): Table
    {
        $query = Schema::hasTable('category_money_flows')
            ? CategoryMoneyFlow::query()
                ->selectRaw('MIN(id) as id, category_key, SUM(CASE WHEN bucket = "platform" THEN platform_amount ELSE 0 END) as our_money, SUM(CASE WHEN bucket = "seller_payout" THEN seller_amount ELSE 0 END) as seller_payouts, SUM(CASE WHEN bucket = "other" THEN gross_amount ELSE 0 END) as other_monies, SUM(gross_amount) as gross, COUNT(*) as transactions')
                ->groupBy('category_key')
                ->orderByDesc(DB::raw('our_money'))
            : CategoryMoneyFlow::query()->whereRaw('1 = 0');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('category_key')
                    ->label('Category')
                    ->formatStateUsing(fn ($state) => MarketplaceCategoryMap::label((string) $state))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('our_money')
                    ->label('Our money')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('seller_payouts')
                    ->label('Seller payouts')
                    ->money('USD')
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('other_monies')
                    ->label('Other')
                    ->money('USD')
                    ->sortable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('gross')
                    ->label('Gross')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactions')
                    ->label('Txns')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
