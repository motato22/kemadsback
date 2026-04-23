<?php

namespace App\Jobs\Advertising;

use App\Models\Advertising\AdvTablet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scheduled via Laravel Scheduler cada 10 minutos.
 * Detecta tablets que no han enviado heartbeat en el tiempo esperado.
 */
class CheckOfflineTabletsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $offlineThresholdMinutes = 10
    ) {
        $this->onQueue('alerts');
    }

    public function handle(): void
    {
        $offlineTablets = AdvTablet::active()
            ->offline($this->offlineThresholdMinutes)
            ->get();

        foreach ($offlineTablets as $tablet) {
            SendHeartbeatAlertJob::dispatch($tablet, 'offline', [
                'last_seen_at'      => $tablet->last_seen_at?->toDateTimeString() ?? 'nunca',
                'threshold_minutes' => $this->offlineThresholdMinutes,
            ]);
        }
    }
}
