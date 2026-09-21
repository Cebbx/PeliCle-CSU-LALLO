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
            ->brandLogo(new \Illuminate\Support\HtmlString(\App\Services\BrandHelper::getBrandHtml('PeliCle', '#0f172a')))
            ->brandLogoHeight('2.5rem')
            ->sidebarWidth('13.5rem')
            ->favicon('/csu-logo-sm.png')
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

                        /* Universal Compact Table Spacing & Fully Visible Action Button */
                        .fi-ta-ctn {
                            overflow: hidden !important;
                            border-radius: 0.75rem !important;
                        }
                        .fi-ta-content {
                            overflow-x: auto !important;
                            padding-right: 2px !important;
                        }
                        .fi-ta-table {
                            width: 100% !important;
                            table-layout: auto !important;
                            border-collapse: separate !important;
                            border-spacing: 0 !important;
                        }
                        .fi-ta-header-cell {
                            padding-left: 4px !important;
                            padding-right: 4px !important;
                            padding-top: 4px !important;
                            padding-bottom: 4px !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-header-cell-label {
                            font-size: 10.5px !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.01em !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-cell {
                            padding-left: 4px !important;
                            padding-right: 4px !important;
                            padding-top: 4px !important;
                            padding-bottom: 4px !important;
                            vertical-align: middle !important;
                        }
                        .fi-ta-header-cell:first-child,
                        .fi-ta-cell:first-child {
                            padding-left: 8px !important;
                        }
                        .fi-ta-text-item-label {
                            font-size: 11px !important;
                            line-height: 1.25 !important;
                        }
                        .fi-ta-text-item-description {
                            font-size: 9px !important;
                            line-height: 1.15 !important;
                        }
                        .fi-badge {
                            font-size: 9px !important;
                            padding: 0.5px 4px !important;
                        }

                        /* Table row hover highlight */
                        .fi-ta-table tbody tr {
                            transition: background-color 0.15s ease-in-out !important;
                        }
                        .fi-ta-table tbody tr:hover,
                        .fi-ta-row:hover {
                            background-color: #f1f5f9 !important;
                        }
                        html.dark .fi-ta-table tbody tr:hover,
                        html.dark .fi-ta-row:hover {
                            background-color: #1e293b !important;
                        }

                        /* Keep short columns compact so table spacing does not artificially stretch */
                        .fi-ta-header-cell[class*="request-number"], .fi-ta-cell[class*="request-number"] {
                            width: 110px !important;
                            max-width: 110px !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-header-cell[class*="employee-name"], .fi-ta-cell[class*="employee-name"] {
                            width: 105px !important;
                            max-width: 105px !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-header-cell[class*="department"], .fi-ta-cell[class*="department"] {
                            width: 85px !important;
                            max-width: 85px !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-header-cell[class*="vehicle"], .fi-ta-cell[class*="vehicle"] {
                            width: 135px !important;
                            max-width: 140px !important;
                        }
                        .fi-ta-header-cell[class*="destination"], .fi-ta-cell[class*="destination"] {
                            width: 120px !important;
                            max-width: 130px !important;
                        }
                        .fi-ta-header-cell[class*="date"], .fi-ta-cell[class*="date"] {
                            width: 110px !important;
                            min-width: 110px !important;
                            max-width: 120px !important;
                            white-space: nowrap !important;
                        }
                        .fi-ta-header-cell[class*="status"], .fi-ta-cell[class*="status"] {
                            width: 130px !important;
                            min-width: 130px !important;
                            max-width: 140px !important;
                            white-space: nowrap !important;
                        }

                        /* Actions Column & Button: Ensure 100% visible, clean and properly proportioned */
                        th.fi-ta-actions-header-cell,
                        td:has(.fi-ta-actions),
                        td.fi-ta-actions-cell {
                            width: 1% !important;
                            white-space: nowrap !important;
                            text-align: right !important;
                            padding-left: 12px !important;
                            padding-right: 16px !important;
                            vertical-align: middle !important;
                        }
                        .fi-ta-actions {
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: flex-end !important;
                            gap: 4px !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            overflow: visible !important;
                        }
                        /* Kebab trigger button ONLY */
                        .fi-ta-actions .fi-dropdown-trigger > button,
                        .fi-ta-actions > .fi-icon-btn {
                            width: 32px !important;
                            height: 32px !important;
                            min-width: 32px !important;
                            min-height: 32px !important;
                            max-width: 32px !important;
                            max-height: 32px !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            border-radius: 0.5rem !important;
                            border: 1px solid #e2e8f0 !important;
                            background-color: #ffffff !important;
                            color: #64748b !important;
                            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                            transition: all 0.15s ease-in-out !important;
                            cursor: pointer !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                        .fi-ta-actions .fi-dropdown-trigger > button:hover,
                        .fi-ta-actions > .fi-icon-btn:hover {
                            background-color: #f8fafc !important;
                            color: #0f172a !important;
                            border-color: #cbd5e1 !important;
                        }
                        html.dark .fi-ta-actions .fi-dropdown-trigger > button,
                        html.dark .fi-ta-actions > .fi-icon-btn {
                            background-color: #0f172a !important;
                            border: 1px solid #334155 !important;
                            color: #94a3b8 !important;
                        }
                        html.dark .fi-ta-actions .fi-dropdown-trigger > button:hover,
                        html.dark .fi-ta-actions > .fi-icon-btn:hover {
                            background-color: #1e293b !important;
                            color: #f8fafc !important;
                            border-color: #475569 !important;
                        }
                        .fi-ta-actions .fi-dropdown-trigger > button svg,
                        .fi-ta-actions > .fi-icon-btn svg {
                            width: 18px !important;
                            height: 18px !important;
                            display: block !important;
                            margin: auto !important;
                        }
                        .fi-ta-actions .fi-btn,
                        .fi-ta-actions-cell .fi-btn {
                            width: auto !important;
                            min-width: unset !important;
                            height: 28px !important;
                            padding: 3px 8px !important;
                            white-space: nowrap !important;
                            font-size: 11px !important;
                        }

                        /* Dropdown menu items: Ensure full width, normal layout, never squished */
                        .fi-dropdown-panel .fi-dropdown-list-item,
                        .fi-dropdown-panel button.fi-dropdown-list-item,
                        .fi-dropdown-panel a.fi-dropdown-list-item {
                            width: 100% !important;
                            height: auto !important;
                            min-width: unset !important;
                            min-height: unset !important;
                            max-width: unset !important;
                            max-height: unset !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: flex-start !important;
                            gap: 8px !important;
                            padding: 8px 12px !important;
                            border-radius: 6px !important;
                            border: none !important;
                            background: transparent !important;
                            box-shadow: none !important;
                            font-size: 13px !important;
                            line-height: 1.25 !important;
                            white-space: nowrap !important;
                            color: inherit !important;
                            transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out !important;
                            cursor: pointer !important;
                        }
                        .fi-dropdown-panel .fi-dropdown-list-item:hover,
                        .fi-dropdown-panel button.fi-dropdown-list-item:hover,
                        .fi-dropdown-panel a.fi-dropdown-list-item:hover {
                            background-color: #f1f5f9 !important;
                            color: #0f172a !important;
                        }
                        html.dark .fi-dropdown-panel .fi-dropdown-list-item:hover,
                        html.dark .fi-dropdown-panel button.fi-dropdown-list-item:hover,
                        html.dark .fi-dropdown-panel a.fi-dropdown-list-item:hover {
                            background-color: #1e293b !important;
                            color: #f8fafc !important;
                        }
                        .fi-dropdown-panel .fi-dropdown-list-item svg {
                            width: 18px !important;
                            height: 18px !important;
                            margin: 0 !important;
                            flex-shrink: 0 !important;
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
