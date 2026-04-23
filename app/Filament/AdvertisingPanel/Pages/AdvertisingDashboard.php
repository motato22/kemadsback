<?php

namespace App\Filament\AdvertisingPanel\Pages;

use App\Filament\AdvertisingPanel\Widgets\FailedMediaAlertWidget;
use Filament\Pages\Dashboard;

class AdvertisingDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $routePath = '/';
    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        // Cualquier usuario autenticado puede acceder al dashboard;
        // los permisos finos se controlan por Shield en recursos/páginas específicas.
        return auth()->check();
    }

    public function getWidgets(): array
    {
        return [
            FailedMediaAlertWidget::class,
            \App\Filament\Widgets\TabletStatusOverview::class,
            \App\Filament\Widgets\TopCampaignsChart::class,
            \App\Filament\Widgets\QrScansChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
