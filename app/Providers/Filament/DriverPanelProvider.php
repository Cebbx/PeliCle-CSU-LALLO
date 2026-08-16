<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\DriverLogin;
use App\Filament\Driver\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DriverPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('driver')
            ->path('driver')
            ->login(DriverLogin::class)
            ->colors([
                'primary' => Color::Orange,
            ])
            ->brandLogo(new \Illuminate\Support\HtmlString('<div class="brand-logo-wrapper" style="display: flex; align-items: center; gap: 8px;"><img src="' . asset('csu-logo.png') . '" style="height: 2.2rem;" /><span class="brand-title-text font-bold text-xl tracking-wider text-slate-800 dark:text-white" style="font-family: \'Outfit\', sans-serif;">PeliCle</span><style>.fi-simple-layout .brand-title-text { display: none !important; } .fi-simple-layout .brand-logo-wrapper { justify-content: center !important; gap: 0 !important; } .fi-simple-layout img { height: 3.5rem !important; }</style></div>'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('csu-logo.png'))
            ->font('Outfit')
            ->discoverResources(in: app_path('Filament/Driver/Resources'), for: 'App\Filament\Driver\Resources')
            ->discoverPages(in: app_path('Filament/Driver/Pages'), for: 'App\Filament\Driver\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Driver/Widgets'), for: 'App\Filament\Driver\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
