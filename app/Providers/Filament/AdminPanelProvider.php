<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->darkMode(true)
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->brandLogo(new \Illuminate\Support\HtmlString('<div class="brand-logo-wrapper" style="display: flex; align-items: center; gap: 8px;"><img src="' . asset('csu-logo.png') . '" style="height: 1.8rem;" /><span class="brand-title-text font-bold text-base tracking-wider" style="font-family: \'Outfit\', sans-serif;">PeliCle</span><style>.fi-simple-layout .brand-title-text { display: none !important; } .fi-simple-layout .brand-logo-wrapper { justify-content: center !important; gap: 0 !important; } .fi-simple-layout img { height: 3.2rem !important; } html.dark .brand-title-text { color: #ffffff !important; } html:not(.dark) .brand-title-text { color: #0f172a !important; }</style></div>'))
            ->brandLogoHeight('2rem')
            ->sidebarWidth('14rem')
            ->favicon(asset('csu-logo.png'))
            ->font('Outfit')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        /* ========================================================
                           1. DARK THEME STYLES (html.dark)
                           ======================================================== */
                        html.dark, html.dark body, html.dark .fi-layout, html.dark .fi-main {
                            background-color: #070a11 !important;
                            color: #f8fafc !important;
                            color-scheme: dark !important;
                        }

                        /* Dark Topbar */
                        html.dark header.fi-topbar, 
                        html.dark .fi-topbar-nav, 
                        html.dark .fi-topbar-header, 
                        html.dark .fi-topbar > div {
                            background-color: #090d16 !important;
                            border-bottom: 1px solid #161f30 !important;
                            height: 44px !important;
                            min-height: 44px !important;
                            max-height: 44px !important;
                        }
                        html.dark .fi-topbar input, 
                        html.dark .fi-global-search-input, 
                        html.dark .fi-global-search-input-field {
                            background-color: #101522 !important;
                            border: 1px solid #1e2a40 !important;
                            color: #ffffff !important;
                            border-radius: 6px !important;
                            height: 28px !important;
                            font-size: 12px !important;
                            padding: 2px 10px !important;
                        }
                        html.dark .fi-topbar input::placeholder {
                            color: #64748b !important;
                            font-size: 11.5px !important;
                        }
                        html.dark .fi-user-avatar {
                            background-color: #1e293b !important;
                            color: #ffffff !important;
                            width: 28px !important;
                            height: 28px !important;
                            font-size: 11.5px !important;
                        }
                        html.dark .fi-topbar svg {
                            color: #94a3b8 !important;
                            width: 18px !important;
                            height: 18px !important;
                        }

                        /* Dark Left Sidebar */
                        html.dark aside.fi-sidebar {
                            width: 14rem !important;
                            background-color: #090d16 !important;
                            border-right: 1px solid #161f30 !important;
                        }
                        html.dark .fi-sidebar-header, 
                        html.dark .fi-sidebar-footer, 
                        html.dark .fi-sidebar-nav {
                            background-color: #090d16 !important;
                            border-color: #161f30 !important;
                        }
                        html.dark .fi-sidebar-header {
                            height: 44px !important;
                            min-height: 44px !important;
                            max-height: 44px !important;
                            padding: 0 14px !important;
                        }
                        html.dark .fi-sidebar-nav {
                            padding: 8px 10px !important;
                        }
                        html.dark .fi-sidebar-item {
                            margin-bottom: 2px !important;
                        }
                        html.dark .fi-sidebar-item a, 
                        html.dark .fi-sidebar-item button {
                            padding: 6px 12px !important;
                            border-radius: 8px !important;
                            color: #cbd5e1 !important;
                            transition: all 0.15s ease-in-out !important;
                        }
                        html.dark .fi-sidebar-item a:hover, 
                        html.dark .fi-sidebar-item button:hover {
                            background-color: #121826 !important;
                            color: #ffffff !important;
                        }
                        html.dark .fi-sidebar-item svg {
                            color: #94a3b8 !important;
                            width: 18px !important;
                            height: 18px !important;
                        }
                        html.dark .fi-sidebar-item span, 
                        html.dark .fi-sidebar-item-label {
                            font-size: 13px !important;
                            font-weight: 500 !important;
                        }
                        html.dark .fi-sidebar-item-active > a, 
                        html.dark .fi-sidebar-item-active > button {
                            background-color: rgba(234, 179, 8, 0.12) !important;
                            color: #facc15 !important;
                            border: 1px solid rgba(234, 179, 8, 0.25) !important;
                        }
                        html.dark .fi-sidebar-item-active svg {
                            color: #facc15 !important;
                        }
                        html.dark .fi-sidebar-item-active span {
                            color: #facc15 !important;
                            font-weight: 700 !important;
                        }
                        html.dark .fi-sidebar-group {
                            margin-top: 8px !important;
                            margin-bottom: 3px !important;
                        }
                        html.dark .fi-sidebar-group-label, 
                        html.dark .fi-sidebar-group-label span {
                            font-size: 10.5px !important;
                            font-weight: 600 !important;
                            color: #64748b !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                        }

                        /* ========================================================
                           2. LIGHT THEME STYLES (html:not(.dark))
                           ======================================================== */
                        html:not(.dark), 
                        html:not(.dark) body, 
                        html:not(.dark) .fi-layout, 
                        html:not(.dark) .fi-main {
                            background-color: #f1f5f9 !important;
                            color: #0f172a !important;
                            color-scheme: light !important;
                        }

                        /* Light Topbar */
                        html:not(.dark) header.fi-topbar, 
                        html:not(.dark) .fi-topbar-nav, 
                        html:not(.dark) .fi-topbar-header, 
                        html:not(.dark) .fi-topbar > div {
                            background-color: #ffffff !important;
                            border-bottom: 1px solid #e2e8f0 !important;
                            height: 44px !important;
                            min-height: 44px !important;
                            max-height: 44px !important;
                        }
                        html:not(.dark) .fi-topbar input, 
                        html:not(.dark) .fi-global-search-input, 
                        html:not(.dark) .fi-global-search-input-field {
                            background-color: #f8fafc !important;
                            border: 1px solid #cbd5e1 !important;
                            color: #0f172a !important;
                            border-radius: 6px !important;
                            height: 28px !important;
                            font-size: 12px !important;
                            padding: 2px 10px !important;
                        }
                        html:not(.dark) .fi-topbar input::placeholder {
                            color: #94a3b8 !important;
                            font-size: 11.5px !important;
                        }
                        html:not(.dark) .fi-user-avatar {
                            background-color: #f1f5f9 !important;
                            color: #0f172a !important;
                            border: 1px solid #cbd5e1 !important;
                            width: 28px !important;
                            height: 28px !important;
                            font-size: 11.5px !important;
                        }
                        html:not(.dark) .fi-topbar svg {
                            color: #475569 !important;
                            width: 18px !important;
                            height: 18px !important;
                        }

                        /* Light Left Sidebar */
                        html:not(.dark) aside.fi-sidebar {
                            width: 14rem !important;
                            background-color: #ffffff !important;
                            border-right: 1px solid #e2e8f0 !important;
                        }
                        html:not(.dark) .fi-sidebar-header, 
                        html:not(.dark) .fi-sidebar-footer, 
                        html:not(.dark) .fi-sidebar-nav {
                            background-color: #ffffff !important;
                            border-color: #e2e8f0 !important;
                        }
                        html:not(.dark) .fi-sidebar-header {
                            height: 44px !important;
                            min-height: 44px !important;
                            max-height: 44px !important;
                            padding: 0 14px !important;
                        }
                        html:not(.dark) .fi-sidebar-nav {
                            padding: 8px 10px !important;
                        }
                        html:not(.dark) .fi-sidebar-item {
                            margin-bottom: 2px !important;
                        }
                        html:not(.dark) .fi-sidebar-item a, 
                        html:not(.dark) .fi-sidebar-item button {
                            padding: 6px 12px !important;
                            border-radius: 8px !important;
                            color: #334155 !important;
                            transition: all 0.15s ease-in-out !important;
                        }
                        html:not(.dark) .fi-sidebar-item a:hover, 
                        html:not(.dark) .fi-sidebar-item button:hover {
                            background-color: #f1f5f9 !important;
                            color: #0f172a !important;
                        }
                        html:not(.dark) .fi-sidebar-item svg {
                            color: #64748b !important;
                            width: 18px !important;
                            height: 18px !important;
                        }
                        html:not(.dark) .fi-sidebar-item span, 
                        html:not(.dark) .fi-sidebar-item-label {
                            font-size: 13px !important;
                            font-weight: 500 !important;
                        }
                        html:not(.dark) .fi-sidebar-item-active > a, 
                        html:not(.dark) .fi-sidebar-item-active > button {
                            background-color: rgba(234, 179, 8, 0.15) !important;
                            color: #b45309 !important;
                            border: 1px solid rgba(234, 179, 8, 0.3) !important;
                        }
                        html:not(.dark) .fi-sidebar-item-active svg {
                            color: #b45309 !important;
                        }
                        html:not(.dark) .fi-sidebar-item-active span {
                            color: #b45309 !important;
                            font-weight: 700 !important;
                        }
                        html:not(.dark) .fi-sidebar-group {
                            margin-top: 8px !important;
                            margin-bottom: 3px !important;
                        }
                        html:not(.dark) .fi-sidebar-group-label, 
                        html:not(.dark) .fi-sidebar-group-label span {
                            font-size: 10.5px !important;
                            font-weight: 600 !important;
                            color: #94a3b8 !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                        }

                        /* 3. Universal Table Spacing & Action Button Polish */
                        .fi-ta-header-cell-label {
                            font-size: 11.5px !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.02em !important;
                        }
                        .fi-ta-cell, .fi-ta-header-cell {
                            padding-left: 8px !important;
                            padding-right: 8px !important;
                            padding-top: 6px !important;
                            padding-bottom: 6px !important;
                        }
                        .fi-ta-text-item-label {
                            font-size: 12px !important;
                        }
                        .fi-ta-text-item-description {
                            font-size: 10.5px !important;
                        }
                        .fi-ta-actions-cell {
                            padding-right: 12px !important;
                            text-align: center !important;
                        }
                    </style>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.components.sidebar-footer'),
            )
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
