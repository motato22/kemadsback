<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvTabletMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncAckController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'downloaded_media_ids'   => ['sometimes', 'array'],
            'downloaded_media_ids.*' => ['integer'],
            'failed_media_ids'       => ['sometimes', 'array'],
            'failed_media_ids.*'     => ['integer'],
            'media_sizes'            => ['sometimes', 'array'],
            'media_sizes.*'          => ['integer'],
        ]);

        /** @var \App\Models\Advertising\AdvTablet $tablet */
        $tablet = $request->user();

        $downloadedIds = $validated['downloaded_media_ids'] ?? [];
        $failedIds     = $validated['failed_media_ids'] ?? [];
        $mediaSizes    = $validated['media_sizes'] ?? []; // [media_id => file_size_kb]

        DB::transaction(function () use ($tablet, $downloadedIds, $failedIds, $mediaSizes) {
            foreach ($downloadedIds as $mediaId) {
                AdvTabletMedia::updateOrCreate(
                    ['tablet_id' => $tablet->id, 'media_id' => $mediaId],
                    [
                        'status'        => 'ready',
                        'downloaded_at' => now(),
                        'file_size_kb'  => $mediaSizes[$mediaId] ?? null,
                    ]
                );
            }

            foreach ($failedIds as $mediaId) {
                AdvTabletMedia::updateOrCreate(
                    ['tablet_id' => $tablet->id, 'media_id' => $mediaId],
                    ['status' => 'failed']
                );
            }
        });

        return response()->json([
            'status'          => 'ok',
            'acknowledged'    => count($downloadedIds),
            'failed'          => count($failedIds),
            'storage_used_kb' => $tablet->storageUsedKb(),
        ]);
    }
}

