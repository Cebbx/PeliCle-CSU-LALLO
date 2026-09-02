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
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn () => new \Illuminate\Support\HtmlString('
                    <style>
                        html.dark, html {
                            background-color: #070a11 !important;
                            color-scheme: dark !important;
                            overflow-y: hidden !important;
                        }
                        aside.fi-sidebar, .fi-sidebar-nav, .fi-sidebar-header, .fi-sidebar-footer {
                            background-color: #090d16 !important;
                            border-color: #141c2c !important;
                        }
                        header.fi-topbar, .fi-topbar-nav {
                            background-color: #090d16 !important;
                            border-color: #141c2c !important;
                            height: 44px !important;
                        }
                        .fi-layout, body {
                            background-color: #070a11 !important;
                            overflow-y: hidden !important;
                        }
                        .fi-main {
                            padding: 0 !important;
                        }
                        .fi-sidebar-header {
                            padding: 6px 12px !important;
                            height: 44px !important;
                        }
                        .fi-sidebar-nav {
                            padding: 2px 6px !important;
                            gap: 1px !important;
                            overflow-y: auto !important;
                        }
                        .fi-sidebar-item {
                            margin-bottom: 1px !important;
                        }
                        .fi-sidebar-item a, .fi-sidebar-item button {
                            padding: 3px 8px !important;
                            min-height: 26px !important;
                            height: 26px !important;
                            border-radius: 6px !important;
                        }
                        .fi-sidebar-item span, .fi-sidebar-item-label {
                            font-size: 11px !important;
                            font-weight: 500 !important;
                        }
                        .fi-sidebar-item svg {
                            width: 14px !important;
                            height: 14px !important;
                        }
                        .fi-sidebar-group {
                            margin-top: 2px !important;
                            margin-bottom: 1px !important;
                        }
                        .fi-sidebar-group-header {
                            padding: 2px 8px !important;
                        }
                        .fi-sidebar-group-label, .fi-sidebar-group-label span {
                            font-size: 9.5px !important;
                            letter-spacing: 0.05em !important;
                        }
                        .fi-sidebar-item-active > a, .fi-sidebar-item-active > button {
                            background-color: rgba(234, 179, 8, 0.12) !important;
                            border-radius: 6px !important;
                        }
                        .fi-sidebar-item-active svg, .fi-sidebar-item-active span {
                            color: #eab308 !important;
                            font-weight: 700 !important;
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
