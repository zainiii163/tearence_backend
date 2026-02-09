<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
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
        $query = DB::table('advertisements')
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->adType !== 'all') {
            $query->where('type', $this->adType);
        }

        return [
            'total_ads' => $query->count(),
            'active_ads' => $query->where('is_active', true)->count(),
            'revenue' => $query->where('payment_status', 'paid')->sum('price'),
            'pending_payment' => $query->where('payment_status', 'pending')->count(),
            'conversion_rate' => $query->count() > 0 
                ? ($query->where('payment_status', 'paid')->count() / $query->count()) * 100 
                : 0,
        ];
    }

    public function getChartData(): array
    {
        $data = DB::table('advertisements')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN price ELSE 0 END) as revenue')
            )
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'dates' => $data->pluck('date')->toArray(),
            'counts' => $data->pluck('count')->toArray(),
            'revenues' => $data->pluck('revenue')->toArray(),
        ];
    }

    public function getTypeDistribution(): array
    {
        $data = DB::table('advertisements')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('type')
            ->get();

        return $data->pluck('count', 'type')->toArray();
    }
}
