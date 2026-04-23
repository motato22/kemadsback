<?php

namespace App\Jobs\Advertising;

use App\Models\Advertising\AdvMedia;
use App\Models\Advertising\AdvTablet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Renueva las URLs firmadas de R2 antes de que expiren.
 * Se ejecuta diariamente via Laravel Scheduler.
 */
class RefreshSignedUrlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $ttl = (int) config('advertising.signed_url_ttl_minutes', 2880);

        AdvMedia::chunk(100, function ($mediaChunk) use ($ttl) {
            foreach ($mediaChunk as $media) {
                $media->refreshSignedUrl(expirationMinutes: $ttl);
            }
        });

        // Forzar re-sync en todas las tablets activas para que reciban las URLs renovadas
        AdvTablet::where('status', 'active')
            ->pluck('id')
            ->each(function (int $tabletId) {
                Cache::put(
                    "adv:sync_required:{$tabletId}",
                    true,
                    now()->addHours(48)
                );
            });
    }
}
