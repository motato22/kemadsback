<?php

namespace App\Filament\Widgets;

use App\Models\Advertising\AdvSurveyResponse;
use App\Models\Advertising\AdvTablet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TabletStatusOverview extends BaseWidget
{
    // Refresco automático cada 60 segundos (ideal para monitoreo)
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $totalTablets = AdvTablet::count();
        // Usamos el scopeOffline que ya había hecho tu equipo (último ping > 10 min)
        $offlineTablets = AdvTablet::offline()->count();
        $onlineTablets = $totalTablets - $offlineTablets;

        return [
            Stat::make('Tablets en Flota', $totalTablets)
                ->description('Total de dispositivos registrados')
                ->descriptionIcon('heroicon-m-device-tablet')
                ->color('primary'),

            Stat::make('En Línea', $onlineTablets)
                ->description('Transmitiendo publicidad')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]), // Simula un pequeño gráfico de actividad

            Stat::make('Offline / Sin reportar', $offlineTablets)
                ->description('Sin conexión hace más de 10 min')
                ->descriptionIcon('heroicon-m-signal-slash')
                ->color($offlineTablets > 0 ? 'danger' : 'gray'),

            Stat::make('Leads Generados', AdvSurveyResponse::whereNotNull('email')->count())
                ->description('Correos capturados en encuestas')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('info'),
        ];
    }
}
