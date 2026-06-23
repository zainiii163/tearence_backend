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
use Illuminate\Support\Facades\Gate;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\VehicleOverviewWidget;
use App\Filament\Widgets\AffiliateOverviewWidget;
use App\Filament\Widgets\AffiliateStatsChart;
use App\Filament\Widgets\RecentAffiliateContent;
use App\Filament\Resources\AdminResource\Widgets\SponsoredOverviewWidget;
use App\Filament\Resources\AdminResource\Widgets\RecentSponsoredAdvertsWidget;
use App\Filament\Resources\AdminResource\Widgets\SponsoredStatsChartWidget;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Define gates for permission-based access control
        Gate::define('view-user-management', function ($user) {
            return $user->is_super_admin || $user->can_manage_users;
        });

        Gate::define('view-analytics', function ($user) {
            return $user->is_super_admin || $user->can_view_analytics;
        });

        Gate::define('view-dashboard', function ($user) {
            return $user->is_super_admin || $user->can_manage_dashboard;
        });

        Gate::define('view-financial', function ($user) {
            return $user->is_super_admin || $user->can_manage_dashboard;
        });
    }

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
                // Events & Venues Widgets
                \App\Filament\Widgets\EventsOverviewWidget::class,
                \App\Filament\Widgets\VenuesOverviewWidget::class,
                \App\Filament\Widgets\RecentEventsWidget::class,
                \App\Filament\Widgets\RecentVenuesWidget::class,
                
                // Property Management Widgets
                \App\Filament\Widgets\PropertyOverviewWidget::class,
                \App\Filament\Widgets\RecentPropertiesWidget::class,
                \App\Filament\Widgets\PropertyEnquiriesWidget::class,
                
                // Banner Management Widgets
                \App\Filament\Widgets\BannerOverviewWidget::class,
                \App\Filament\Widgets\RecentBannersWidget::class,
                
                // Other widgets (temporarily disabled to prevent timeout issues)
                // \App\Filament\Widgets\JobsOverviewWidget::class,
                // \App\Filament\Widgets\RevenueOverviewWidget::class,
                // \App\Filament\Widgets\CandidatesOverviewWidget::class,
                // \App\Filament\Widgets\UpsellsOverviewWidget::class,
                // \App\Filament\Widgets\ServicesOverviewWidget::class,
                // \App\Filament\Widgets\PromotedAdvertsOverviewWidget::class,
                // \App\Filament\Widgets\RecentPromotedAdvertsWidget::class,
                // \App\Filament\Widgets\PromotedAdvertsStatsWidget::class,
                // \App\Filament\Widgets\RevenueChartWidget::class,
                // \App\Filament\Widgets\JobsChartWidget::class,
                // \App\Filament\Widgets\ServicesChartWidget::class,
                // \App\Filament\Widgets\RecentJobsWidget::class,
                // \App\Filament\Widgets\RecentUpsellsWidget::class,
                // \App\Filament\Widgets\RecentServicesWidget::class,
                // \App\Filament\Widgets\RecentPromotionsWidget::class,
                // \App\Filament\Widgets\FundingOverviewWidget::class,
                // \App\Filament\Widgets\FundingChartWidget::class,
                // \App\Filament\Widgets\RecentFundingProjectsWidget::class,
                // VehicleOverviewWidget::class,
                // AffiliateOverviewWidget::class,
                // AffiliateStatsChart::class,
                // RecentAffiliateContent::class,
                // SponsoredOverviewWidget::class,
                // RecentSponsoredAdvertsWidget::class,
                // SponsoredStatsChartWidget::class,
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
            ])
            ->navigationGroups([
                'Content Management',
                'Banner Management',
                'Property Hub',
                'Events & Venues',
                'Services Management',
                'Monetization',
                'Analytics',
                'Settings',
                'Admin Management',
            ])
            ->renderHook(
                'panels::head.end',
                fn () => '<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>'
            );
    }
}
