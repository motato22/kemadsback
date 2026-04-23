<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class QrScansChart extends ChartWidget
{
    protected static ?string $heading = 'Interacciones QR (Últimos 7 días)';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($daysAgo) => now()->subDays($daysAgo)->format('Y-m-d'));

        $scans = DB::table('adv_qr_scans')
            ->select(DB::raw('DATE(scanned_at) as date'), DB::raw('count(*) as total'))
            ->where('scanned_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date');

        $data = $days->map(fn ($day) => $scans->get($day, 0))->toArray();
        $labels = $days->map(fn ($day) => Carbon::parse($day)->format('d/m'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Escaneos de pasajeros',
                    'data' => $data,
                    'borderColor' => '#002557',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(0, 37, 87, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
