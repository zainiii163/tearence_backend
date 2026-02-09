<?php

namespace App\Providers;

use App\Filament\Resources\AdPricingPlanResource;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\AffiliateResource;
use App\Filament\Resources\RevenueTrackingResource;
use App\Filament\Widgets\MonetizationOverviewWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Gray,
                'success' => Color::Green,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'info' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'))
            ->discoverWidgets(in: app_path('Filament/Widgets'))
            ->pages([
                Pages\Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                StartSession::class,
                Authenticate::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ], web: true)
            ->navigationGroups([
                'Content Management',
                'Monetization',
                'Analytics',
                'Settings',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->brandName('WWA Admin')
            ->brandLogo(asset('logo.png'))
            ->favicon(asset('favicon.png'))
            ->renderHook(
                'panels::head.end',
                fn (): string => '<meta name="description" content="WWA Admin Panel - Manage your advertising platform">'
            );
    }
}
