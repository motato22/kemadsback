<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advertising\HeartbeatRequest;
use App\Jobs\Advertising\ProcessHeartbeatJob;
use App\Models\Advertising\AdvTablet;
use App\Services\Advertising\TabletCommandService;
use Illuminate\Http\JsonResponse;

class HeartbeatController extends Controller
{
    /**
     * POST /api/adv/heartbeat
     *
     * Canal principal de comunicación tablet → servidor.
     * Registra el estado de la tablet y devuelve comandos pendientes.
     * El procesamiento pesado (alertas, análisis) se delega a un Job asíncrono.
     */
    public function store(HeartbeatRequest $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        // Actualización inmediata de last_seen_at y datos vitales en la misma request
        $tablet->update([
            'last_seen_at'     => now(),
            'battery_level'    => $request->battery_level,
            'app_version'      => $request->app_version,
            'guardian_active'  => $request->guardian_active ?? true,
            'player_installed' => $request->player_installed ?? true,
            'player_version'   => $request->player_version,
        ]);

        // El log completo y las alertas se procesan en background para no bloquear la respuesta
        ProcessHeartbeatJob::dispatch($tablet, $request->validated());

        $commands = $this->buildCommandsForTablet($tablet);

        return response()->json([
            'status'       => 'ok',
            'server_time'  => now()->toIso8601String(),
            'commands'     => $commands,
            'sync_required' => $this->isSyncRequired($tablet),
        ]);
    }

    private function buildCommandsForTablet(AdvTablet $tablet): array
    {
        $commands = [];

        // Comandos dinámicos pendientes desde el panel
        $pending = TabletCommandService::flush($tablet);
        if (! empty($pending)) {
            $commands = array_merge($commands, $pending);
        }

        // Verifica si hay una versión nueva de APK disponible desde config (OTA)
        $latestApkVersion = config('advertising.latest_apk_version');
        $latestApkUrl     = config('advertising.latest_apk_url');
        $latestApkSha256  = config('advertising.latest_apk_sha256');

        if ($latestApkVersion && $tablet->app_version !== $latestApkVersion && $latestApkUrl) {
            $commands[] = [
                'type'    => 'update_app',
                'apk_url' => $latestApkUrl,
                'sha256'  => $latestApkSha256,
                'version' => $latestApkVersion,
            ];
        }

        return $commands;
    }

    private function isSyncRequired(AdvTablet $tablet): bool
    {
        // Usa caché para evitar queries en cada heartbeat (cada 3 min por tablet)
        $cacheKey = "adv:sync_required:{$tablet->id}";

        return (bool) cache()->get($cacheKey, false);
    }
}
