<?php

namespace App\Filament\Resources\BuySellAdvertResource\Pages;

use App\Filament\Resources\BuySellAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use Illuminate\Support\Facades\DB;

class BuySellAdvertStats extends Page
{
    protected static string $resource = BuySellAdvertResource::class;

    protected static string $view = 'filament.resources.buy-sell-advert-resource.pages.buy-sell-advert-stats';

    public function mount(): void
    {
        // Add any required mount logic here
    }

    public function getStatsData(): array
    {
        return [
            'total_adverts' => BuySellAdvert::count(),
            'active_adverts' => BuySellAdvert::where('status', 'active')->count(),
            'sold_adverts' => BuySellAdvert::where('status', 'sold')->count(),
            'expired_adverts' => BuySellAdvert::where('status', 'expired')->count(),
            'featured_adverts' => BuySellAdvert::where('featured', true)->count(),
            'promoted_adverts' => BuySellAdvert::where('is_promoted', true)->count(),
            'total_views' => BuySellAdvert::sum('views_count'),
            'total_saves' => BuySellAdvert::sum('saves_count'),
            'total_contacts' => BuySellAdvert::sum('contacts_count'),
            'categories_count' => BuySellCategory::count(),
        ];
    }

    public function getCategoryStats(): array
    {
        return BuySellAdvert::join('buysell_categories', 'buysell_adverts.category_id', '=', 'buysell_categories.id')
            ->selectRaw('buysell_categories.name, COUNT(*) as count')
            ->groupBy('buysell_categories.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getCountryStats(): array
    {
        return BuySellAdvert::selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
