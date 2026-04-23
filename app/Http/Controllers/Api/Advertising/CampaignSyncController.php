<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvCampaign;
use App\Models\Advertising\AdvMedia;
use App\Models\Advertising\AdvTablet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CampaignSyncController extends Controller
{
    /**
     * GET /api/adv/campaigns/sync
     *
     * Devuelve el catálogo completo de campañas activas con sus media para la tablet.
     * La tablet compara contra su Room local y descarga solo lo que le falta.
     * Respuesta cacheada por tablet para evitar N+1 en heartbeats simultáneos.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $cacheKey = "adv:sync_payload:{$tablet->id}";
        $ttl      = now()->addMinutes(5);

        $payload = Cache::remember($cacheKey, $ttl, fn () => $this->buildSyncPayload($tablet));

        // Limpia el flag de sync_required ahora que la tablet solicitó sincronización
        Cache::forget("adv:sync_required:{$tablet->id}");

        return response()->json($payload);
    }

    private function buildSyncPayload(AdvTablet $tablet): array
    {
        // Cambiamos 'survey.questions' por 'survey.questions.options' (Sistema A)
        $campaigns = AdvCampaign::with(['media', 'survey.questions.options'])
            ->active()
            ->forTablet($tablet)
            ->orderBy('sort_order')
            ->get();

        $activeMediaIds = $campaigns->flatMap(fn ($c) => $c->media->pluck('id'))->all();

        return [
            'campaigns'         => $campaigns->map(fn ($campaign) => [
                'id'         => $campaign->id,
                'name'       => $campaign->name,
                'starts_at'  => $campaign->starts_at->toIso8601String(),
                'ends_at'    => $campaign->ends_at->toIso8601String(),

                // Variables del QR
                'has_qr'     => $campaign->has_qr,
                'qr_url'     => $campaign->has_qr ? route('adv.campaigns.qr', $campaign->id) : null,

                'media'      => $campaign->media->map(fn ($media) => [
                    'id'            => $media->id,
                    'type'          => $media->type,
                    'cdn_url'       => $media->cdn_url,
                    'md5_hash'      => $media->md5_hash,
                    'file_size_kb'  => $media->file_size_kb,
                    'duration_secs' => $media->duration_secs,
                    'sort_order'    => $media->sort_order,
                ]),
                'survey'     => $campaign->survey ? [
                    'id'                 => $campaign->survey->id,
                    'name'               => $campaign->survey->name,
                    'type'               => $campaign->survey->type,
                    'timeout_seconds'    => $campaign->survey->timeout_seconds,

                    // Mapeo adaptado al Sistema A (AdvQuestion + AdvOption)
                    'questions'          => $campaign->survey->questions->map(fn ($q) => [
                        'id'            => $q->id,
                        'question_text' => $q->question_text,
                        'type'          => $q->type,
                        'sort_order'    => $q->sort_order,
                        'options'       => $q->options->map(fn ($opt) => [
                            'id'          => $opt->id,
                            'option_text' => $opt->option_text,
                            // is_correct NO se envía a la app para evitar trampas
                        ]),
                    ]),
                ] : null,
            ]),
            'obsolete_media_ids' => $this->calculateObsoleteMediaIds($tablet, $activeMediaIds),
            'storage_budget_kb'  => config('advertising.storage_budget_kb'),
        ];
    }

    private function calculateObsoleteMediaIds(AdvTablet $tablet, array $activeMediaIds): array
    {
        $hasTracking = $tablet->tabletMedia()->exists();

        if ($hasTracking) {
            // Cálculo basado en lo que la tablet tiene registrado como descargado (status ready)
            return $tablet->media()
                ->wherePivot('status', 'ready')
                ->whereNotIn('adv_media.id', $activeMediaIds)
                ->pluck('adv_media.id')
                ->values()
                ->all();
        }

        // Fallback: campañas que alguna vez aplicaron a la tablet (histórico), menos las activas actuales
        return AdvMedia::whereHas('campaign', function ($q) use ($tablet) {
                $q->forTablet($tablet);
            })
            ->whereNotIn('id', $activeMediaIds)
            ->pluck('id')
            ->values()
            ->all();
    }
}
