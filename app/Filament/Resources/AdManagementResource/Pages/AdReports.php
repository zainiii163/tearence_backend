<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use App\Models\Advertisement;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdReports extends ResourcePage implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = AdManagementResource::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.resources.ad-management.pages.reports';

    protected static ?string $title = 'Ad Reports';

    protected static ?string $navigationLabel = 'Reports';

    public function table(Table $table): Table
    {
        $adsTable = (new Advertisement)->getTable();
        $query = Advertisement::query()->whereRaw('1 = 0');

        if (Schema::hasTable($adsTable)) {
            $hasPayment = Schema::hasColumn($adsTable, 'payment_status');
            $hasPrice = Schema::hasColumn($adsTable, 'price');
            $hasType = Schema::hasColumn($adsTable, 'type');

            $select = [
                DB::raw('COUNT(*) as total_ads'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ads'),
                DB::raw($hasPayment && $hasPrice
                    ? 'SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as total_revenue'
                    : '0 as total_revenue'),
                DB::raw($hasPayment
                    ? 'SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_payments'
                    : '0 as pending_payments'),
                DB::raw($hasPrice ? 'AVG(price) as avg_price' : '0 as avg_price'),
            ];

            if ($hasType) {
                array_unshift($select, 'type');
                $query = Advertisement::query()->select($select)->groupBy('type');
            } else {
                array_unshift($select, DB::raw("'all' as type"));
                $query = Advertisement::query()->select($select);
            }
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Ad Type')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : '—')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('total_ads')
                    ->label('Total Ads')
                    ->sortable()
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('active_ads')
                    ->label('Active Ads')
                    ->sortable()
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pending_payments')
                    ->label('Pending Payments')
                    ->sortable()
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('avg_price')
                    ->label('Average Price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(now()->subDays(30)),
                        \Filament\Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.ad-management.reports.details', ['type' => $record->type])),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    // Export logic here
                }),
            
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    // PDF export logic here
                }),
        ];
    }

    public function getSummaryStats(): array
    {
        $table = (new Advertisement)->getTable();
        if (! Schema::hasTable($table)) {
            return [
                'total_ads' => 0,
                'active_ads' => 0,
                'total_revenue' => 0,
                'pending_payments' => 0,
            ];
        }

        $hasPayment = Schema::hasColumn($table, 'payment_status');
        $hasPrice = Schema::hasColumn($table, 'price');

        $summary = DB::table($table)
            ->select([
                DB::raw('COUNT(*) as total_ads'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ads'),
                DB::raw($hasPayment && $hasPrice
                    ? 'SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as total_revenue'
                    : '0 as total_revenue'),
                DB::raw($hasPayment
                    ? 'SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_payments'
                    : '0 as pending_payments'),
            ])
            ->first();

        return [
            'total_ads' => $summary->total_ads ?? 0,
            'active_ads' => $summary->active_ads ?? 0,
            'total_revenue' => $summary->total_revenue ?? 0,
            'pending_payments' => $summary->pending_payments ?? 0,
        ];
    }
}
