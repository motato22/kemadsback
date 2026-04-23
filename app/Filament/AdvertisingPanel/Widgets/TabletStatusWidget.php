<?php

namespace App\Filament\AdvertisingPanel\Widgets;

use App\Models\Advertising\AdvTablet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TabletStatusWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total   = AdvTablet::where('status', 'active')->count();
        $online  = AdvTablet::active()->where('last_seen_at', '>=', now()->subMinutes(10))->count();
        $offline = $total - $online;

        return [
            Stat::make('Tablets Activas', $total)
                ->description('Total en flota')
                ->icon('heroicon-o-device-tablet')
                ->color('success'),

            Stat::make('En Línea', $online)
                ->description('Heartbeat < 10 min')
                ->icon('heroicon-o-signal')
                ->color('success'),

            Stat::make('Offline', $offline)
                ->description('Sin heartbeat reciente')
                ->icon('heroicon-o-signal-slash')
                ->color($offline > 0 ? 'danger' : 'gray'),
        ];
    }
}
