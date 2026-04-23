<?php

namespace App\Models\Advertising;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdvTablet extends Model
{
    use HasApiTokens;

    protected $table = 'adv_tablets';

    protected $fillable = [
        'device_id',
        'unit_id',
        'name',
        'status',
        'last_seen_at',
        'battery_level',
        'app_version',
        'sanctum_token_id',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'battery_level' => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function heartbeatLogs(): HasMany
    {
        return $this->hasMany(AdvHeartbeatLog::class, 'tablet_id');
    }

    public function playbackLogs(): HasMany
    {
        return $this->hasMany(AdvPlaybackLog::class, 'tablet_id');
    }

    public function driverShifts(): HasMany
    {
        return $this->hasMany(AdvDriverShift::class, 'tablet_id');
    }

    public function activeShift(): HasOne
    {
        return $this->hasOne(AdvDriverShift::class, 'tablet_id')
            ->whereNull('ended_at')
            ->latest('started_at');
    }

    public function campaignGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvCampaignGroup::class,
            'adv_campaign_group_tablet',
            'tablet_id',
            'campaign_group_id'
        );
    }

    /**
     * Relación directa al pivot de media por tablet (tracking de storage).
     */
    public function tabletMedia(): HasMany
    {
        return $this->hasMany(AdvTabletMedia::class, 'tablet_id');
    }

    /**
     * Relación many-to-many a AdvMedia con metadatos en el pivot.
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(AdvMedia::class, 'adv_tablet_media', 'tablet_id', 'media_id')
            ->withPivot(['downloaded_at', 'file_size_kb', 'status'])
            ->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOffline(Builder $query, int $minutesThreshold = 10): Builder
    {
        return $query->where('last_seen_at', '<', Carbon::now()->subMinutes($minutesThreshold))
            ->orWhereNull('last_seen_at');
    }

    public function scopeLowBattery(Builder $query, int $threshold = 20): Builder
    {
        return $query->where('battery_level', '<=', $threshold)
            ->whereNotNull('battery_level');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isOnline(int $minutesThreshold = 10): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->greaterThan(Carbon::now()->subMinutes($minutesThreshold));
    }

    public function markAsSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    /**
     * IDs de media confirmados como descargados (status ready) para esta tablet.
     */
    public function readyMediaIds(): array
    {
        return $this->media()
            ->wherePivot('status', 'ready')
            ->pluck('adv_media.id')
            ->all();
    }

    /**
     * Storage total usado en KB por media en estado ready.
     */
    public function storageUsedKb(): int
    {
        return (int) $this->tabletMedia()
            ->where('status', 'ready')
            ->sum('file_size_kb');
    }
}
