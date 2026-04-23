<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AdvDriverShift extends Model
{
    protected $table = 'adv_driver_shifts';

    protected $fillable = [
        'tablet_id',
        'driver_name',
        'driver_code',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }

    public function surveyResults(): HasMany
    {
        return $this->hasMany(AdvSurveyResult::class, 'driver_shift_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function close(): void
    {
        $this->update(['ended_at' => now()]);
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
