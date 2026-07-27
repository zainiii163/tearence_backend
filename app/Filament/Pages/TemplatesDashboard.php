<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BusinessTemplateResource;
use App\Models\BusinessTemplate;
use App\Models\TemplatePurchase;
use App\Models\TemplateSetting;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class TemplatesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Templates Dashboard';

    protected static ?string $title = 'Business Templates';

    protected static string $view = 'filament.pages.templates-dashboard';

    protected static ?string $navigationGroup = 'Templates';

    protected static ?int $navigationSort = 0;

    public static function canAccess(): bool
    {
        return true;
    }

    public function getViewData(): array
    {
        $hasTemplates = Schema::hasTable('business_templates');
        $hasPurchases = Schema::hasTable('template_purchases');

        return [
            'premium_monthly_fee' => TemplateSetting::premiumMonthlyFee(),
            'premium_duration_days' => TemplateSetting::premiumDurationDays(),
            'stats' => [
                'total' => $hasTemplates ? BusinessTemplate::count() : 0,
                'active' => $hasTemplates ? BusinessTemplate::where('status', 'active')->count() : 0,
                'premium' => $hasTemplates ? BusinessTemplate::query()->premiumActive()->count() : 0,
                'catalog' => $hasTemplates ? BusinessTemplate::where('is_catalog', true)->count() : 0,
                'seller' => $hasTemplates ? BusinessTemplate::where('is_catalog', false)->count() : 0,
                'purchases' => $hasPurchases
                    ? TemplatePurchase::where('payment_status', 'completed')->count()
                    : 0,
                'revenue' => $hasPurchases
                    ? (float) TemplatePurchase::where('payment_status', 'completed')->sum('platform_fee')
                    : 0,
            ],
            'recent' => $hasTemplates
                ? BusinessTemplate::query()->latest()->take(8)->get()
                : collect(),
            'recent_purchases' => $hasPurchases
                ? TemplatePurchase::query()->latest()->take(8)->get()
                : collect(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('all_templates')
                ->label('All templates')
                ->icon('heroicon-o-document-duplicate')
                ->url(BusinessTemplateResource::getUrl('index')),
            Action::make('create_template')
                ->label('Create template')
                ->icon('heroicon-o-plus')
                ->url(BusinessTemplateResource::getUrl('create')),
            Action::make('pricing')
                ->label('Premium pricing')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->url(TemplatePricingSettings::getUrl()),
        ];
    }
}
