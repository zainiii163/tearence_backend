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
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->sidebarCollapsibleOnDesktop()
            ->pages([
                \App\Filament\Pages\AdminDashboard::class,
                \App\Filament\Pages\TemplatesDashboard::class,
                \App\Filament\Pages\TemplatePricingSettings::class,
                \App\Filament\Clusters\DashboardCluster\Pages\AnalyticsOverview::class,
                \App\Filament\Clusters\DashboardCluster\Pages\RevenueOverview::class,
                \App\Filament\Clusters\DashboardCluster\Pages\AffiliatePayouts::class,
                \App\Filament\Clusters\DashboardCluster\Pages\MarketplaceSnapshot::class,
                \App\Filament\Clusters\AdvertAnalyticsCluster\Pages\FeaturedAdvertsAnalytics::class,
                \App\Filament\Clusters\AdvertAnalyticsCluster\Pages\SponsoredAdvertsAnalytics::class,
                \App\Filament\Clusters\AdvertAnalyticsCluster\Pages\PromotedAdvertsAnalytics::class,
                \App\Filament\Clusters\AdvertAnalyticsCluster\Pages\SiteWideFeedTotals::class,
            ])
            ->homeUrl(fn () => \App\Filament\Clusters\DashboardCluster\Pages\AnalyticsOverview::getUrl())
            ->resources([
                \App\Filament\Resources\BusinessTemplateResource::class,
                \App\Filament\Resources\TemplatePurchaseResource::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Heavy widgets live under DashboardCluster pages — keep panel widget list empty
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
                'User Management',
                'Content Management',
                'Templates',
                'Banner Management',
                'Property Hub',
                'Events & Venues',
                'Services Management',
                'Buy & Sell',
                'Monetization',
                'Affiliates Hub',
                'Settings',
            ])
            ->renderHook(
                'panels::head.end',
                fn () => '<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>'
            );
    }
}
