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
            ->authGuard('employee')
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
                        /* -------------------------------------------------------------
                           1. ACTIVE & EDITABLE INPUTS (Fields user needs to fill up)
                           ------------------------------------------------------------- */
                        .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)) {
                            border: 1.5px solid #94a3b8 !important;
                            border-radius: 0.5rem !important;
                            background-color: #ffffff !important;
                            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
                        }

                        html.dark .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)) {
                            border: 1.5px solid rgba(255, 255, 255, 0.22) !important;
                            background-color: rgba(255, 255, 255, 0.04) !important;
                        }

                        /* Hover on editable fields */
                        .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)):hover {
                            border-color: #d97706 !important;
                        }

                        html.dark .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)):hover {
                            border-color: #f59e0b !important;
                        }

                        /* Focus / Active on editable fields - Amber Glow */
                        .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)):focus-within {
                            border-color: #d97706 !important;
                            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.2) !important;
                            outline: none !important;
                        }

                        html.dark .fi-input-wrp:not(.fi-disabled):not(:has(input:disabled)):not(:has(input[readonly])):not(:has(textarea:disabled)):not(:has(select:disabled)):focus-within {
                            border-color: #fbbf24 !important;
                            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.25) !important;
                            outline: none !important;
                        }

                        .fi-input-wrp:not(.fi-disabled) input:not([readonly]):not(:disabled) {
                            color: #0f172a !important;
                            cursor: text !important;
                        }

                        html.dark .fi-input-wrp:not(.fi-disabled) input:not([readonly]):not(:disabled) {
                            color: #f8fafc !important;
                        }

                        /* -------------------------------------------------------------
                           2. LOCKED / DEFAULT / SYSTEM-GENERATED BOXES
                           (Request Number, Department, Destination Preview)
                           ------------------------------------------------------------- */
                        .fi-input-wrp.fi-disabled,
                        .fi-input-wrp:has(input:disabled),
                        .fi-input-wrp:has(input[readonly]),
                        .fi-input-wrp:has(textarea:disabled),
                        .fi-input-wrp:has(select:disabled) {
                            border: 1px solid #cbd5e1 !important;
                            border-radius: 0.5rem !important;
                            background-color: #f1f5f9 !important; /* Soft Slate-100 */
                            cursor: not-allowed !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        html.dark .fi-input-wrp.fi-disabled,
                        html.dark .fi-input-wrp:has(input:disabled),
                        html.dark .fi-input-wrp:has(input[readonly]),
                        html.dark .fi-input-wrp:has(textarea:disabled),
                        html.dark .fi-input-wrp:has(select:disabled) {
                            border: 1px solid rgba(255, 255, 255, 0.1) !important;
                            background-color: rgba(15, 23, 42, 0.6) !important; /* Subdued Dark Slate */
                            cursor: not-allowed !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        .fi-input-wrp.fi-disabled:focus-within,
                        .fi-input-wrp:has(input:disabled):focus-within,
                        .fi-input-wrp:has(input[readonly]):focus-within {
                            border-color: #cbd5e1 !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        html.dark .fi-input-wrp.fi-disabled:focus-within,
                        html.dark .fi-input-wrp:has(input:disabled):focus-within,
                        html.dark .fi-input-wrp:has(input[readonly]):focus-within {
                            border-color: rgba(255, 255, 255, 0.1) !important;
                            box-shadow: none !important;
                            outline: none !important;
                        }

                        .fi-input-wrp.fi-disabled input,
                        .fi-input-wrp:has(input:disabled) input,
                        .fi-input-wrp:has(input[readonly]) input,
                        input[readonly],
                        input:disabled {
                            cursor: not-allowed !important;
                            color: #475569 !important; /* Muted Slate-600 */
                            font-weight: 500 !important;
                        }

                        html.dark .fi-input-wrp.fi-disabled input,
                        html.dark .fi-input-wrp:has(input:disabled) input,
                        html.dark .fi-input-wrp:has(input[readonly]) input,
                        html.dark input[readonly],
                        html.dark input:disabled {
                            color: #94a3b8 !important; /* Slate-400 */
                        }

                        /* -------------------------------------------------------------
                           3. REQUIRED FIELD ASTERISK & HELPER TEXT HIGHLIGHT
                           ------------------------------------------------------------- */
                        .fi-fo-field-wrp-label sup,
                        .fi-fo-field-wrp-label span[class*="text-danger"],
                        .text-danger-600 {
                            color: #ef4444 !important;
                            font-weight: 700 !important;
                        }

                        .fi-fo-field-wrp-helper-text {
                            font-size: 0.75rem !important;
                            line-height: 1rem !important;
                            margin-top: 0.25rem !important;
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
                \App\Http\Middleware\SetPanelAuthGuard::class,
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
