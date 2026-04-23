<?php

use App\Models\Advertising\AdvCampaign;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Módulo Advertising: activar/expirar campañas por fechas ─────────────────
Schedule::call(function () {
    $now = now();

    // 1. Activar las campañas programadas que ya llegaron a su fecha de inicio
    $activated = AdvCampaign::where('status', 'scheduled')
        ->where('starts_at', '<=', $now)
        ->update(['status' => 'active']);

    // 2. Expirar las campañas activas que ya superaron su fecha de fin
    $expired = AdvCampaign::where('status', 'active')
        ->where('ends_at', '<', $now)
        ->update(['status' => 'expired']);

    // 3. Si hubo algún cambio, limpiamos la caché para que las tablets se enteren
    if ($activated > 0 || $expired > 0) {
        Cache::tags(['adv:sync'])->flush();
    }
})->everyMinute()->name('adv-campaign-status-manager')->withoutOverlapping();
