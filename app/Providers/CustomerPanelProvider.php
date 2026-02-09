<?php

namespace App\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CustomerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('customer')
            ->path('customer-admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/CustomerResources'), for: 'App\\Filament\\CustomerResources')
            ->discoverPages(in: app_path('Filament/CustomerPages'), for: 'App\\Filament\\CustomerPages')
            ->pages([
                \App\Filament\CustomerPages\CustomerDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/CustomerWidgets'), for: 'App\\Filament\\CustomerWidgets')
            ->widgets([
                \App\Filament\CustomerWidgets\CustomerListingsOverview::class,
                \App\Filament\CustomerWidgets\CustomerUpsellsOverview::class,
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
                \App\Http\Middleware\CustomerAdminMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web')
            ->maxContentWidth('full')
            ->sidebarCollapsibleOnDesktop();
    }
}
