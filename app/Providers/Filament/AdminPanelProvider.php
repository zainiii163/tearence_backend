<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin-web')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\AdminDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\BannerOverviewWidget::class,
                \App\Filament\Widgets\RecentBannersWidget::class,
                \App\Filament\Widgets\JobsOverviewWidget::class,
                \App\Filament\Widgets\RevenueOverviewWidget::class,
                \App\Filament\Widgets\CandidatesOverviewWidget::class,
                \App\Filament\Widgets\UpsellsOverviewWidget::class,
                \App\Filament\Widgets\ServicesOverviewWidget::class,
                \App\Filament\Widgets\PromotedAdvertsOverviewWidget::class,
                \App\Filament\Widgets\RecentPromotedAdvertsWidget::class,
                \App\Filament\Widgets\PromotedAdvertsStatsWidget::class,
                \App\Filament\Widgets\RevenueChartWidget::class,
                \App\Filament\Widgets\JobsChartWidget::class,
                \App\Filament\Widgets\ServicesChartWidget::class,
                \App\Filament\Widgets\RecentJobsWidget::class,
                \App\Filament\Widgets\RecentUpsellsWidget::class,
                \App\Filament\Widgets\RecentServicesWidget::class,
                \App\Filament\Widgets\RecentPromotionsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
