<?php

namespace App\Filament\Pages;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePromotion;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class ServicesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Services Dashboard';

    protected static ?string $title = 'Services Dashboard';

    protected static string $view = 'filament.pages.services-dashboard';

    protected static ?string $navigationGroup = 'Services Management';

    protected static ?int $navigationSort = 0;

    public function getViewData(): array
    {
        return [
            'stats' => [
                'total_services' => Service::count(),
                'active_services' => Service::where('status', 'active')->count(),
                'pending_services' => Service::where('status', 'pending')->count(),
                'total_categories' => ServiceCategory::where('is_active', true)->count(),
                'promoted_services' => Service::whereNotNull('promotion_type')->count(),
                'total_revenue' => ServicePromotion::sum('price'),
                'recent_services' => Service::with(['user', 'category'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'popular_categories' => ServiceCategory::withCount('services')
                    ->orderBy('services_count', 'desc')
                    ->take(5)
                    ->get(),
                'recent_promotions' => ServicePromotion::with(['service', 'service.user'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'services_by_type' => Service::selectRaw('service_type, COUNT(*) as count')
                    ->groupBy('service_type')
                    ->get(),
                'promotion_revenue' => ServicePromotion::selectRaw('promotion_type, SUM(price) as revenue, COUNT(*) as count')
                    ->groupBy('promotion_type')
                    ->get(),
            ],
        ];
    }

    protected function getActions(): array
    {
        return [
            Action::make('create_service')
                ->label('Create Service')
                ->icon('heroicon-o-plus')
                ->url(route('filament.admin.resources.services.create')),
                
            Action::make('manage_categories')
                ->label('Manage Categories')
                ->icon('heroicon-o-tag')
                ->url(route('filament.admin.resources.service-categories.index')),
                
            Action::make('view_all_services')
                ->label('All Services')
                ->icon('heroicon-o-list-bullet')
                ->url(route('filament.admin.resources.services.index')),
        ];
    }
}
