<?php

namespace App\Filament\AdvertisingPanel\Widgets;

use App\Models\Advertising\AdvTablet;
use App\Models\Advertising\AdvTabletMedia;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FailedMediaAlertWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tabletsWithFailed = AdvTablet::whereHas('tabletMedia', fn ($q) => $q->where('status', 'failed'))->count();

        $totalFailed = AdvTabletMedia::where('status', 'failed')->count();

        $totalReady = AdvTabletMedia::where('status', 'ready')->count();

        return [
            Stat::make('Tablets con media fallida', $tabletsWithFailed)
                ->description($tabletsWithFailed > 0 ? 'Requieren atención' : 'Todo en orden')
                ->color($tabletsWithFailed > 0 ? 'danger' : 'success')
                ->icon($tabletsWithFailed > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),

            Stat::make('Media fallida total', $totalFailed)
                ->description('Archivos sin descargar')
                ->color($totalFailed > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-x-circle'),

            Stat::make('Media lista', $totalReady)
                ->description('Archivos confirmados')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}

