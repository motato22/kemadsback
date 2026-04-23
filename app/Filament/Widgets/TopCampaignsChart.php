<?php

namespace App\Filament\Widgets;

use App\Models\Advertising\AdvCampaign;
use Filament\Widgets\ChartWidget;

class TopCampaignsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Campañas (Impresiones)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $campaigns = AdvCampaign::withCount('playbackLogs')
            ->orderByDesc('playback_logs_count')
            ->take(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Reproducciones Totales',
                    'data' => $campaigns->pluck('playback_logs_count')->toArray(),
                    'backgroundColor' => '#6CCAFF',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $campaigns->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
