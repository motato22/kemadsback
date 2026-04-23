<?php

namespace App\Jobs\Advertising;

use App\Models\Advertising\AdvTablet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendHeartbeatAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly AdvTablet $tablet,
        public readonly string $alertType,
        public readonly array $context = []
    ) {
        $this->onQueue('alerts');
    }

    public function handle(): void
    {
        // Anti-flood: evita enviar la misma alerta más de una vez cada 30 minutos
        $throttleKey = "adv:alert:{$this->alertType}:{$this->tablet->id}";

        if (Cache::has($throttleKey)) {
            return;
        }

        Cache::put($throttleKey, true, now()->addMinutes(30));

        $this->sendAlert();
    }

    private function sendAlert(): void
    {
        $adminEmail = config('advertising.alert_email');

        if (! $adminEmail) {
            Log::warning("[ADV] Alerta {$this->alertType} sin destinatario configurado.", [
                'tablet'  => $this->tablet->unit_id,
                'context' => $this->context,
            ]);
            return;
        }

        $subject = match ($this->alertType) {
            'low_battery'      => "⚠ Batería baja — Tablet {$this->tablet->unit_id}",
            'offline'          => "🔴 Tablet offline — {$this->tablet->unit_id}",
            'rollback_executed' => "🔄 Rollback ejecutado — {$this->tablet->unit_id}",
            default            => "Alerta sistema — {$this->tablet->unit_id}",
        };

        Mail::raw(
            $this->buildMessage(),
            fn ($message) => $message
                ->to($adminEmail)
                ->subject($subject)
        );
    }

    private function buildMessage(): string
    {
        $lines = [
            "Tablet: {$this->tablet->name} (Unidad {$this->tablet->unit_id})",
            "Device ID: {$this->tablet->device_id}",
            "Tipo de alerta: {$this->alertType}",
            "Fecha/hora: " . now()->toDateTimeString(),
            "",
        ];

        foreach ($this->context as $key => $value) {
            $lines[] = "{$key}: {$value}";
        }

        return implode("\n", $lines);
    }
}
