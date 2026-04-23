<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvTabletMedia extends Model
{
    protected $table = 'adv_tablet_media';

    protected $fillable = [
        'tablet_id',
        'media_id',
        'file_size_kb',
        'downloaded_at',
        'status',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'file_size_kb'  => 'integer',
    ];

    // Scopes
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDownloading($query)
    {
        return $query->where('status', 'downloading');
    }

    // Relations
    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(AdvMedia::class, 'media_id');
    }
}

