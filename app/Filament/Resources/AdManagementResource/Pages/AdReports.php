<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        return $table
            ->query(
                DB::table('advertisements')
                    ->select([
                        'type',
                        DB::raw('COUNT(*) as total_ads'),
                        DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ads'),
                        DB::raw('SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as total_revenue'),
                        DB::raw('SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_payments'),
                        DB::raw('AVG(price) as avg_price'),
                    ])
                    ->groupBy('type')
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Ad Type')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
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
        $summary = DB::table('advertisements')
            ->select([
                DB::raw('COUNT(*) as total_ads'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_ads'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as total_revenue'),
                DB::raw('SUM(CASE WHEN payment_status = "pending" THEN 1 ELSE 0 END) as pending_payments'),
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
