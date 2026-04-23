<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class AdvMedia extends Model
{
    protected $table = 'adv_media';

    protected $fillable = [
        'campaign_id',
        'type',
        'filename',
        'storage_path',
        'cdn_url',
        'md5_hash',
        'file_size_kb',
        'duration_secs',
        'sort_order',
    ];

    protected $casts = [
        'file_size_kb'  => 'integer',
        'duration_secs' => 'integer',
        'sort_order'    => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvCampaign::class, 'campaign_id');
    }

    public function tablets(): BelongsToMany
    {
        return $this->belongsToMany(AdvTablet::class, 'adv_tablet_media', 'media_id', 'tablet_id')
            ->withPivot(['downloaded_at', 'file_size_kb', 'status'])
            ->withTimestamps();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Genera una URL firmada fresca desde Cloudflare R2.
     * Se llama desde el Observer al crear/actualizar, y periódicamente via Job.
     */
    public function refreshSignedUrl(int $expirationMinutes = 1440): void
    {
        $signedUrl = Storage::disk('r2')->temporaryUrl(
            $this->storage_path,
            now()->addMinutes($expirationMinutes)
        );

        $this->update(['cdn_url' => $signedUrl]);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}
