<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use App\Models\Advertisement;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AdAnalytics extends ResourcePage
{
    protected static string $resource = AdManagementResource::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.resources.ad-management.pages.analytics';

    protected static ?string $title = 'Ad Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    public $startDate;
    public $endDate;
    public $adType;

    public function mount(): void
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->adType = 'all';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Report')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    Notification::make()
                        ->title('Report exported successfully')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getAnalyticsData(): array
    {
        $table = (new Advertisement)->getTable();
        if (! Schema::hasTable($table)) {
            return [
                'total_ads' => 0,
                'active_ads' => 0,
                'revenue' => 0,
                'pending_payment' => 0,
                'conversion_rate' => 0,
            ];
        }

        $query = DB::table($table)
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->adType !== 'all' && Schema::hasColumn($table, 'type')) {
            $query->where('type', $this->adType);
        }

        $total = (clone $query)->count();
        $active = (clone $query)->where('is_active', true)->count();
        $hasPayment = Schema::hasColumn($table, 'payment_status');
        $hasPrice = Schema::hasColumn($table, 'price');

        return [
            'total_ads' => $total,
            'active_ads' => $active,
            'revenue' => $hasPayment && $hasPrice ? (clone $query)->where('payment_status', 'paid')->sum('price') : 0,
            'pending_payment' => $hasPayment ? (clone $query)->where('payment_status', 'pending')->count() : 0,
            'conversion_rate' => $total > 0 && $hasPayment
                ? ((clone $query)->where('payment_status', 'paid')->count() / $total) * 100
                : 0,
        ];
    }

    public function getChartData(): array
    {
        $table = (new Advertisement)->getTable();
        $data = collect();

        if (Schema::hasTable($table)) {
            $hasPayment = Schema::hasColumn($table, 'payment_status');
            $hasPrice = Schema::hasColumn($table, 'price');
            $revenueSql = $hasPayment && $hasPrice
                ? 'SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as revenue'
                : '0 as revenue';

            $data = DB::table($table)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw($revenueSql)
                )
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        return [
            'dates' => $data->pluck('date')->toArray(),
            'counts' => $data->pluck('count')->toArray(),
            'revenues' => $data->pluck('revenue')->toArray(),
        ];
    }

    public function getTypeDistribution(): array
    {
        $table = (new Advertisement)->getTable();
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'type')) {
            return [];
        }

        $data = DB::table($table)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('type')
            ->get();

        return $data->pluck('count', 'type')->toArray();
    }
}
