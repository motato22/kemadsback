<?php

namespace App\Filament\AdvertisingPanel;

use App\Filament\AdvertisingPanel\Pages\AdvertisingDashboard;
use App\Filament\AdvertisingPanel\Pages\ManageGuardianApk;
use App\Filament\AdvertisingPanel\Pages\ManagePlayerApk;
use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource;
use App\Filament\AdvertisingPanel\Resources\AdvReportResource;
use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource;
use App\Filament\AdvertisingPanel\Resources\AdvTabletResource;
use App\Filament\AdvertisingPanel\Resources\UserResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panel Filament completamente aislado del sistema existente.
 * Ruta: /adv-panel — Credenciales propias, sin tocar el panel principal.
 */
class AdvertisingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('advertising')
            ->path('adv-panel')
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
            ->brandLogoHeight('15rem')
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
            ->resources([
                AdvTabletResource::class,
                AdvCampaignResource::class,
                AdvAdvertiserResource::class,
                AdvCampaignGroupResource::class,
                AdvSurveyResource::class,
                AdvReportResource::class,
                UserResource::class,
            ])
            ->pages([
                AdvertisingDashboard::class,
                ManageGuardianApk::class,
                ManagePlayerApk::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\TabletStatusOverview::class,
                \App\Filament\Widgets\TopCampaignsChart::class,
                \App\Filament\Widgets\QrScansChart::class,
                \App\Filament\AdvertisingPanel\Widgets\FailedMediaAlertWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
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
