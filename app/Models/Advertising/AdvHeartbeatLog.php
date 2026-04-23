<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvHeartbeatLog extends Model
{
    public $timestamps = false;

    protected $table = 'adv_heartbeat_logs';

    protected $fillable = [
        'tablet_id',
        'reported_at',
        'received_at',
        'battery_level',
        'app_version',
        'lat',
        'lng',
        'raw_payload',
    ];

    protected $casts = [
        'reported_at'   => 'datetime',
        'received_at'   => 'datetime',
        'battery_level' => 'integer',
        'lat'           => 'float',
        'lng'           => 'float',
        'raw_payload'   => 'array',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }
}
