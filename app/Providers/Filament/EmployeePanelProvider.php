<?php

namespace App\Providers\Filament;

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

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employee')
            ->path('employee')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(new \Illuminate\Support\HtmlString('<div class="brand-logo-wrapper" style="display: flex; align-items: center; gap: 8px;"><img src="' . asset('csu-logo.png') . '" style="height: 2.2rem;" /><span class="brand-title-text font-bold text-xl tracking-wider text-slate-800 dark:text-white" style="font-family: \'Outfit\', sans-serif;">PeliCle</span><style>.fi-simple-layout .brand-title-text { display: none !important; } .fi-simple-layout .brand-logo-wrapper { justify-content: center !important; gap: 0 !important; } .fi-simple-layout img { height: 3.5rem !important; }</style></div>'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('csu-logo.png'))
            ->font('Outfit')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        /* Eliminate yellow outline/ring and highlight on locked, readonly, and disabled boxes */
                        input:disabled,
                        input[readonly],
                        .fi-input-wrp:has(input:disabled),
                        .fi-input-wrp:has(input[readonly]),
                        .fi-input-wrp.fi-disabled,
                        .fi-locked-field,
                        .fi-locked-field * {
                            outline: none !important;
                            box-shadow: none !important;
                            --tw-ring-color: transparent !important;
                            --tw-ring-shadow: none !important;
                            ring: 0 !important;
                        }
                        .fi-input-wrp:has(input:disabled):focus-within,
                        .fi-input-wrp:has(input[readonly]):focus-within,
                        .fi-input-wrp.fi-disabled:focus-within,
                        .fi-locked-field:focus-within {
                            outline: none !important;
                            box-shadow: none !important;
                            --tw-ring-color: transparent !important;
                            --tw-ring-shadow: none !important;
                            border-color: rgba(255, 255, 255, 0.15) !important;
                        }
                        .fi-locked-field,
                        .fi-locked-field input {
                            pointer-events: none !important;
                            cursor: default !important;
                            user-select: none !important;
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Employee/Resources'), for: 'App\Filament\Employee\Resources')
            ->discoverPages(in: app_path('Filament/Employee/Pages'), for: 'App\Filament\Employee\Pages')
            ->pages([
                \App\Filament\Employee\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Employee/Widgets'), for: 'App\Filament\Employee\Widgets')
            ->widgets([
                \App\Filament\Employee\Widgets\EmployeeStatsOverview::class,
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
