<?php

namespace App\Observers\Advertising;

use App\Models\Advertising\AdvMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Observer de AdvMedia.
 * Calcula el MD5 del archivo al crear, regenera URL firmada y limpia caché de sync.
 */
class AdvMediaObserver
{
    public function creating(AdvMedia $media): void
    {
        // Deriva filename del storage_path si no viene del formulario
        if (empty($media->filename) && filled($media->storage_path)) {
            $media->filename = basename($media->storage_path);
        }

        // Intenta obtener el tamaño del archivo recién subido
        if (empty($media->file_size_kb) && filled($media->storage_path)) {
            try {
                $bytes = Storage::disk('r2')->size($media->storage_path);
                $media->file_size_kb = (int) ceil($bytes / 1024);
            } catch (\Throwable) {
                $media->file_size_kb = 0;
            }
        }
    }

    public function created(AdvMedia $media): void
    {
        $this->computeMd5($media);
        $media->refreshSignedUrl(expirationMinutes: 2880);
        $this->invalidateSyncCache();
    }

    public function updated(AdvMedia $media): void
    {
        if ($media->isDirty('storage_path')) {
            $this->computeMd5($media);
            $media->refreshSignedUrl(expirationMinutes: 2880);
        }

        $this->invalidateSyncCache();
    }

    public function deleted(AdvMedia $media): void
    {
        // Elimina el archivo físico de R2 al borrar el registro
        Storage::disk('r2')->delete($media->storage_path);
        $this->invalidateSyncCache();
    }

    private function computeMd5(AdvMedia $media): void
    {
        try {
            $content = Storage::disk('r2')->get($media->storage_path);
            $hash    = md5($content);
            $media->updateQuietly(['md5_hash' => $hash]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("[ADV] Error calculando MD5 de media {$media->id}: {$e->getMessage()}");
        }
    }

    private function invalidateSyncCache(): void
    {
        // Invalida todos los payloads de sync cacheados para forzar re-sincronización
        Cache::tags(['adv:sync'])->flush();
    }
}
