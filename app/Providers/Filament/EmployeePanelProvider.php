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
            ->databaseNotifications()
            ->databaseNotificationsPolling('3s')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        /* Ensure all input boxes have clearly visible rounded borders and clean backgrounds */
                        .fi-input-wrp {
                            border: 1px solid rgba(255, 255, 255, 0.16) !important;
                            border-radius: 0.5rem !important;
                            background-color: rgba(255, 255, 255, 0.03) !important;
                        }

                        html:not(.dark) .fi-input-wrp {
                            border: 1px solid #d1d5db !important;
                            border-radius: 0.5rem !important;
                            background-color: #ffffff !important;
                        }

                        /* Prevent yellow/amber focus ring when clicking read-only or disabled boxes */
                        .fi-input-wrp:has(input[readonly]):focus-within,
                        .fi-input-wrp:has(input:disabled):focus-within,
                        .fi-input-wrp.fi-disabled:focus-within {
                            border-color: rgba(255, 255, 255, 0.25) !important;
                            --tw-ring-color: transparent !important;
                            --tw-ring-shadow: none !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        html:not(.dark) .fi-input-wrp:has(input[readonly]):focus-within,
                        html:not(.dark) .fi-input-wrp:has(input:disabled):focus-within,
                        html:not(.dark) .fi-input-wrp.fi-disabled:focus-within {
                            border-color: #9ca3af !important;
                            --tw-ring-color: transparent !important;
                            --tw-ring-shadow: none !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        input[readonly],
                        input:disabled {
                            cursor: default !important;
                            color: #f8fafc !important;
                        }

                        html:not(.dark) input[readonly],
                        html:not(.dark) input:disabled {
                            color: #0f172a !important;
                        }

                        input[readonly]:focus,
                        input:disabled:focus {
                            outline: none !important;
                            box-shadow: none !important;
                        }
                    </style>
                    <script>
                        document.addEventListener("livewire:init", function () {
                            if (window.Livewire && window.Livewire.hook) {
                                Livewire.hook("request", function (context) {
                                    if (context && context.fail) {
                                        context.fail(function (error) {
                                            if (error && error.status === 419) {
                                                if (typeof error.preventDefault === "function") {
                                                    error.preventDefault();
                                                }
                                                window.location.reload();
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    </script>
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
