<?php

namespace App\Jobs\Advertising;

use App\Models\Advertising\AdvHeartbeatLog;
use App\Models\Advertising\AdvTablet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessHeartbeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly AdvTablet $tablet,
        public readonly array $payload
    ) {
        $this->onQueue('heartbeats');
    }

    public function handle(): void
    {
        // 1. Persiste el log completo del heartbeat
        AdvHeartbeatLog::create([
            'tablet_id'     => $this->tablet->id,
            'reported_at'   => $this->payload['reported_at'] ?? now(),
            'received_at'   => now(),
            'battery_level' => $this->payload['battery_level'] ?? null,
            'app_version'   => $this->payload['app_version'] ?? null,
            'lat'           => $this->payload['lat'] ?? null,
            'lng'           => $this->payload['lng'] ?? null,
            'raw_payload'   => $this->payload,
        ]);

        // 2. Alerta de batería baja
        if (isset($this->payload['battery_level']) && $this->payload['battery_level'] <= 20) {
            SendHeartbeatAlertJob::dispatch($this->tablet, 'low_battery', [
                'battery_level' => $this->payload['battery_level'],
            ]);
        }
    }
}
