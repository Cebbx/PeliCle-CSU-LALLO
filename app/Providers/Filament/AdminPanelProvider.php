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
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->brandLogo(new \Illuminate\Support\HtmlString('<div class="brand-logo-wrapper" style="display: flex; align-items: center; gap: 8px;"><img src="' . asset('csu-logo.png') . '" style="height: 2.2rem;" /><span class="brand-title-text font-bold text-xl tracking-wider text-white" style="font-family: \'Outfit\', sans-serif;">PeliCle</span><style>.fi-simple-layout .brand-title-text { display: none !important; } .fi-simple-layout .brand-logo-wrapper { justify-content: center !important; gap: 0 !important; } .fi-simple-layout img { height: 3.5rem !important; }</style></div>'))
            ->brandLogoHeight('2.5rem')
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
                    <script>
                        document.documentElement.classList.add("dark");
                        try { localStorage.setItem("theme", "dark"); } catch(e) {}
                    </script>
                    <style>
                        /* Suppress any scrollbars completely */
                        html, body, .fi-layout, .fi-main, .fi-main-ctn {
                            background-color: #070a11 !important;
                            color: #f8fafc !important;
                            color-scheme: dark !important;
                            overflow-y: hidden !important;
                        }
                        *::-webkit-scrollbar {
                            display: none !important;
                            width: 0px !important;
                            height: 0px !important;
                        }
                        * {
                            -ms-overflow-style: none !important;
                            scrollbar-width: none !important;
                        }

                        /* 2. Top Header Navigation Bar */
                        header.fi-topbar, .fi-topbar-nav, .fi-topbar-header {
                            background-color: #090d16 !important;
                            border-bottom: 1px solid #161f30 !important;
                        }
                        .fi-topbar input, .fi-global-search-input, .fi-global-search-input-field {
                            background-color: #101522 !important;
                            border: 1px solid #1e2a40 !important;
                            color: #ffffff !important;
                            border-radius: 8px !important;
                        }
                        .fi-topbar input::placeholder {
                            color: #64748b !important;
                        }
                        .fi-user-avatar {
                            background-color: #1e293b !important;
                            color: #ffffff !important;
                        }

                        /* 3. Left Navigation Sidebar */
                        aside.fi-sidebar, .fi-sidebar-header, .fi-sidebar-footer, .fi-sidebar-nav {
                            background-color: #090d16 !important;
                            border-right: 1px solid #161f30 !important;
                        }
                        .fi-sidebar-nav {
                            padding: 8px 10px !important;
                        }
                        .fi-sidebar-item {
                            margin-bottom: 2px !important;
                        }
                        .fi-sidebar-item a, .fi-sidebar-item button {
                            padding: 6px 12px !important;
                            border-radius: 8px !important;
                            color: #cbd5e1 !important;
                            transition: all 0.15s ease-in-out !important;
                        }
                        .fi-sidebar-item a:hover, .fi-sidebar-item button:hover {
                            background-color: #121826 !important;
                            color: #ffffff !important;
                        }
                        .fi-sidebar-item svg {
                            color: #94a3b8 !important;
                            width: 18px !important;
                            height: 18px !important;
                        }
                        .fi-sidebar-item span {
                            font-size: 12.5px !important;
                            font-weight: 500 !important;
                        }

                        /* Active Sidebar Item (Subtle Amber/Gold) */
                        .fi-sidebar-item-active > a, .fi-sidebar-item-active > button {
                            background-color: rgba(234, 179, 8, 0.12) !important;
                            color: #facc15 !important;
                            border: 1px solid rgba(234, 179, 8, 0.25) !important;
                        }
                        .fi-sidebar-item-active svg {
                            color: #facc15 !important;
                        }
                        .fi-sidebar-item-active span {
                            color: #facc15 !important;
                            font-weight: 700 !important;
                        }

                        /* Groups */
                        .fi-sidebar-group {
                            margin-top: 8px !important;
                            margin-bottom: 4px !important;
                        }
                        .fi-sidebar-group-label, .fi-sidebar-group-label span {
                            font-size: 11px !important;
                            font-weight: 600 !important;
                            color: #64748b !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
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
