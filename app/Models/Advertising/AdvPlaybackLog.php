<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvPlaybackLog extends Model
{
    protected $table = 'adv_playback_logs';

    protected $fillable = [
        'tablet_id',
        'campaign_id',
        'started_at',
        'ended_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvCampaign::class, 'campaign_id');
    }
}
