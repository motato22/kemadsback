<?php

namespace App\Jobs\Advertising;

use App\Models\Advertising\AdvPlaybackLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        public readonly string $reportType,
        public readonly array $filters,
        public readonly string $notifyEmail
    ) {
        $this->onQueue('exports');
    }

    public function handle(): void
    {
        $filename = "reports/adv_{$this->reportType}_" . now()->format('Ymd_His') . '.csv';

        $data = $this->fetchReportData();

        Storage::disk('r2')->put($filename, $this->buildCsv($data));

        $signedUrl = Storage::disk('r2')->temporaryUrl($filename, now()->addHours(24));

        \Illuminate\Support\Facades\Mail::raw(
            "Su reporte está listo. Descárguelo aquí (válido 24h):\n\n{$signedUrl}",
            fn ($m) => $m->to($this->notifyEmail)->subject('Reporte listo — KEMADVERTISING')
        );
    }

    private function fetchReportData(): \Illuminate\Support\Collection
    {
        return match ($this->reportType) {
            'playbacks' => $this->playbacksReport(),
            default     => collect(),
        };
    }

    private function playbacksReport(): \Illuminate\Support\Collection
    {
        return AdvPlaybackLog::with(['tablet', 'campaign'])
            ->when(isset($this->filters['from']), fn ($q) => $q->where('started_at', '>=', $this->filters['from']))
            ->when(isset($this->filters['to']), fn ($q) => $q->where('started_at', '<=', $this->filters['to']))
            ->when(isset($this->filters['tablet_id']), fn ($q) => $q->where('tablet_id', $this->filters['tablet_id']))
            ->orderBy('started_at')
            ->get()
            ->map(fn ($log) => [
                'inicio' => $log->started_at->toDateTimeString(),
                'fin' => $log->ended_at?->toDateTimeString() ?? '-',
                'duracion_seg' => $log->duration_seconds,
                'unidad' => $log->tablet->unit_id,
                'campana' => $log->campaign->name ?? '-',
            ]);
    }

    private function buildCsv(\Illuminate\Support\Collection $data): string
    {
        if ($data->isEmpty()) {
            return '';
        }

        $headers = array_keys($data->first());
        $lines   = [$headers];

        foreach ($data as $row) {
            $lines[] = array_values($row);
        }

        return collect($lines)
            ->map(fn ($row) => implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)))
            ->implode("\n");
    }
}
