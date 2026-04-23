<?php

namespace App\Exports;

use App\Models\Advertising\AdvCampaign;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonthlyCampaignReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected int $campaignId,
        protected ?string $desde = null,
        protected ?string $hasta = null
    ) {}

    public function collection()
    {
        $desdeStart = $this->desde ? $this->desde . ' 00:00:00' : null;
        $hastaEnd = $this->hasta ? $this->hasta . ' 23:59:59' : null;

        $query = AdvCampaign::with('advertiser')
            ->withCount([
                'playbackLogs as playback_logs_count' => function ($q) use ($desdeStart, $hastaEnd) {
                    if ($desdeStart) {
                        $q->where('started_at', '>=', $desdeStart);
                    }
                    if ($hastaEnd) {
                        $q->where('started_at', '<=', $hastaEnd);
                    }
                },
            ])
            ->withSum([
                'playbackLogs as total_duration_seconds' => function ($q) use ($desdeStart, $hastaEnd) {
                    if ($desdeStart) {
                        $q->where('started_at', '>=', $desdeStart);
                    }
                    if ($hastaEnd) {
                        $q->where('started_at', '<=', $hastaEnd);
                    }
                },
            ], 'duration_seconds')
            ->withCount([
                'qrScans as qr_scans_count' => function ($q) use ($desdeStart, $hastaEnd) {
                    if ($desdeStart) {
                        $q->where('scanned_at', '>=', $desdeStart);
                    }
                    if ($hastaEnd) {
                        $q->where('scanned_at', '<=', $hastaEnd);
                    }
                },
            ])
            ->withCount([
                'surveyResponses as survey_responses_count' => function ($q) use ($desdeStart, $hastaEnd) {
                    $q->whereNotNull('email');
                    if ($desdeStart) {
                        $q->where('completed_at', '>=', $desdeStart);
                    }
                    if ($hastaEnd) {
                        $q->where('completed_at', '<=', $hastaEnd);
                    }
                },
            ])
            ->where('id', $this->campaignId);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Anunciante',
            'Campaña',
            'Impresiones',
            'Tiempo en pantalla (min)',
            'Escaneos QR',
            'Leads',
        ];
    }

    public function map($row): array
    {
        $duration = (float) ($row->total_duration_seconds ?? 0);

        return [
            $row->advertiser?->name ?? '—',
            $row->name,
            $row->playback_logs_count ?? 0,
            number_format($duration / 60, 2),
            $row->qr_scans_count ?? 0,
            $row->survey_responses_count ?? 0,
        ];
    }
}
