<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
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
                'primary' => Color::hex('#002557'),
                'info' => Color::hex('#6CCAFF'),
                'accent' => Color::hex('#2370D8'),
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'gray' => Color::Slate,
            ])
            ->font('Montserrat')
            ->brandName('KEMADS')
            ->brandLogo(asset('images/ISOTIPO-KEMADS-MARINO.png'))
            ->darkModeBrandLogo(asset('images/ISOTIPO-KEMADS-BLANCO.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/favicon.ico'))
            ->defaultThemeMode(ThemeMode::Light)
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn (): string => view('components.kemads-login-logo')->render())
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '
                    <style>
                        .fi-sidebar {
                            background-color: #002557 !important;
                        }

                        .fi-sidebar-nav-group-label,
                        .fi-sidebar-item-label {
                            color: #e2e8f0 !important;
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-label {
                            color: #ffffff !important;
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-button {
                            background-color: rgba(255, 255, 255, 0.15) !important;
                        }

                        .fi-sidebar-item-button:hover {
                            background-color: rgba(255, 255, 255, 0.10) !important;
                        }

                        .fi-sidebar-item-icon {
                            color: #93c5fd !important;
                        }

                        .fi-sidebar-item-active .fi-sidebar-item-icon {
                            color: #ffffff !important;
                        }

                        .dark .fi-sidebar {
                            background-color: #001a3d !important;
                        }
                    </style>
                ',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\TabletStatusOverview::class,
                \App\Filament\Widgets\TopCampaignsChart::class,
                \App\Filament\Widgets\QrScansChart::class,
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
