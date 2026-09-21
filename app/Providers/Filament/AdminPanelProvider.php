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
            ->authGuard('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->darkMode(true)
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->brandLogo(new \Illuminate\Support\HtmlString(\App\Services\BrandHelper::getBrandHtml('PeliCle', '#0f172a')))
            ->brandLogoHeight('2.6rem')
            ->sidebarWidth('14rem')
            ->favicon('/csu-logo-sm.png')
            ->font('Outfit')
            ->databaseNotifications()
            ->databaseNotificationsPolling('3s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        /* Zero Box / Zero Border on CSU Logo + PeliCle Brand */
                        .fi-logo, 
                        a.fi-logo, 
                        .brand-logo-wrapper, 
                        .fi-sidebar-header,
                        .fi-sidebar-header > *,
                        .fi-sidebar-header a, 
                        .fi-topbar-header,
                        .fi-topbar-header > *,
                        .fi-topbar a,
                        .fi-logo:hover,
                        a.fi-logo:hover,
                        .brand-logo-wrapper:hover {
                            background: transparent !important;
                            background-color: transparent !important;
                            box-shadow: none !important;
                            border: none !important;
                            outline: none !important;
                            text-decoration: none !important;
                        }

                        /* ========================================================
                           1. DARK THEME STYLES (html.dark)
                           ======================================================== */
                        html.dark, 
                        html.dark body, 
                        html.dark .fi-layout, 
                        html.dark .fi-main,
                        html.dark .fi-page,
                        html.dark .fi-topbar,
                        html.dark header.fi-topbar, 
                        html.dark .fi-topbar-nav, 
                        html.dark .fi-topbar-header, 
                        html.dark .fi-topbar > div,
                        html.dark aside.fi-sidebar,
                        html.dark .fi-sidebar,
                        html.dark .fi-sidebar-header, 
                        html.dark .fi-sidebar-footer, 
                        html.dark .fi-sidebar-nav {
                            background-color: #070a11 !important;
                            background: #070a11 !important;
                            color: #f8fafc !important;
                            color-scheme: dark !important;
                        }

                        /* Dark Topbar */
                        html.dark header.fi-topbar, 
                        html.dark .fi-topbar,
                        html.dark .fi-topbar-nav, 
                        html.dark .fi-topbar-header, 
                        html.dark .fi-topbar > div {
                            border-bottom: 1px solid #161f30 !important;
                            height: 50px !important;
                            min-height: 50px !important;
                            max-height: 50px !important;
                        }
                        html.dark .fi-global-search-field input,
                        html.dark .fi-topbar input, 
                        html.dark .fi-global-search-input, 
                        html.dark .fi-global-search-input-field {
                            background-color: #0b0f19 !important;
                            border: 1px solid #1e293b !important;
                            border-radius: 6px !important;
                            color: #ffffff !important;
                            height: 30px !important;
                            font-size: 11.5px !important;
                            padding-left: 30px !important;
                            padding-right: 10px !important;
                            width: 210px !important;
                        }
                        html.dark .fi-topbar input::placeholder {
                            color: #64748b !important;
                            font-size: 11px !important;
                        }
                        html.dark .fi-global-search svg,
                        html.dark .fi-global-search-field svg {
                            color: #64748b !important;
                            width: 14px !important;
                            height: 14px !important;
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
                        html.dark .fi-icon-btn-badge {
                            background-color: #ef4444 !important;
                            color: #ffffff !important;
                            font-size: 9.5px !important;
                            font-weight: 700 !important;
                        }

                        /* Dark Left Sidebar */
                        html.dark aside.fi-sidebar {
                            width: 14rem !important;
                            border-right: 1px solid #161f30 !important;
                        }
                        html.dark .fi-sidebar-header, 
                        html.dark .fi-sidebar-footer, 
                        html.dark .fi-sidebar-nav {
                            border-color: #161f30 !important;
                        }
                        html.dark .fi-sidebar-header {
                            height: 50px !important;
                            min-height: 50px !important;
                            max-height: 50px !important;
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
                            height: 50px !important;
                            min-height: 50px !important;
                            max-height: 50px !important;
                        }
                        html:not(.dark) .fi-global-search-field input,
                        html:not(.dark) .fi-topbar input, 
                        html:not(.dark) .fi-global-search-input, 
                        html:not(.dark) .fi-global-search-input-field {
                            background-color: #f1f5f9 !important;
                            border: 1px solid #cbd5e1 !important;
                            border-radius: 6px !important;
                            color: #0f172a !important;
                            height: 30px !important;
                            font-size: 11.5px !important;
                            padding-left: 30px !important;
                            padding-right: 10px !important;
                            width: 210px !important;
                        }
                        html:not(.dark) .fi-topbar input::placeholder {
                            color: #64748b !important;
                            font-size: 11px !important;
                        }
                        html:not(.dark) .fi-global-search svg,
                        html:not(.dark) .fi-global-search-field svg {
                            color: #64748b !important;
                            width: 14px !important;
                            height: 14px !important;
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
                            height: 50px !important;
                            min-height: 50px !important;
                            max-height: 50px !important;
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

                        /* 3. Universal Compact Table Spacing & Fully Visible Action Button */
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
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.components.sidebar-footer'),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn () => new \Illuminate\Support\HtmlString('
                    <script>
                        (function() {
                            function playNotificationChime() {
                                try {
                                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                    const now = ctx.currentTime;
                                    const osc1 = ctx.createOscillator();
                                    const gain1 = ctx.createGain();
                                    osc1.type = "sine";
                                    osc1.frequency.setValueAtTime(659.25, now);
                                    gain1.gain.setValueAtTime(0.12, now);
                                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                                    osc1.connect(gain1);
                                    gain1.connect(ctx.destination);
                                    osc1.start(now);
                                    osc1.stop(now + 0.25);

                                    const osc2 = ctx.createOscillator();
                                    const gain2 = ctx.createGain();
                                    osc2.type = "sine";
                                    osc2.frequency.setValueAtTime(987.77, now + 0.1);
                                    gain2.gain.setValueAtTime(0.15, now + 0.1);
                                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                                    osc2.connect(gain2);
                                    gain2.connect(ctx.destination);
                                    osc2.start(now + 0.1);
                                    osc2.stop(now + 0.45);
                                } catch (e) {}
                            }

                            let lastNotifCount = null;
                            setInterval(() => {
                                const badgeEl = document.querySelector(".fi-no-database .fi-badge, .fi-topbar-database-notifications-btn .fi-badge");
                                const count = badgeEl ? parseInt(badgeEl.textContent.trim()) || 0 : 0;
                                if (lastNotifCount !== null && count > lastNotifCount) {
                                    playNotificationChime();
                                }
                                lastNotifCount = count;
                            }, 1500);
                        })();
                    </script>
                ')
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
