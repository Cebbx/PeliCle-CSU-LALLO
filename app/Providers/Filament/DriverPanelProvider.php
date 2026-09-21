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
            ->authGuard('driver')
            ->colors([
                'primary' => Color::Orange,
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

                        /* Actions Column & Button: Ensure 100% visible, clean and properly proportioned */
                        th.fi-ta-actions-header-cell,
                        td:has(.fi-ta-actions),
                        td.fi-ta-actions-cell {
                            width: 1% !important;
                            white-space: nowrap !important;
                            text-align: right !important;
                            padding-left: 6px !important;
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
                ')
            )
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
