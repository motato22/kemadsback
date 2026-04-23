<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advertising\PlaybackEventRequest;
use App\Models\Advertising\AdvPlaybackLog;
use App\Models\Advertising\AdvTablet;
use Illuminate\Http\JsonResponse;

class PlaybackEventController extends Controller
{
    /**
     * POST /api/adv/playback-event
     *
     * Registra reproducciones completadas. La tablet puede enviar lotes (batched events)
     * acumulados durante períodos offline. Se insertan en bulk para eficiencia.
     */
    public function store(PlaybackEventRequest $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $events = $request->input('events', []);

        $records = collect($events)->map(fn ($event) => [
            'tablet_id' => $tablet->id,
            'campaign_id' => $event['campaign_id'],
            'started_at' => $event['started_at'],
            'ended_at' => $event['ended_at'] ?? null,
            'duration_seconds' => $event['duration_seconds'] ?? 0,
        ])->all();

        AdvPlaybackLog::insert($records);

        return response()->json([
            'status'   => 'ok',
            'recorded' => count($records),
        ]);
    }
}
