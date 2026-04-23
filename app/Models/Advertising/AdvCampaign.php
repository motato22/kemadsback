<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdvCampaign extends Model
{
    protected $table = 'adv_campaigns';

    protected $fillable = [
        'advertiser_id',
        'name',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
        'has_qr',
        'qr_url',
        'qr_scans',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'has_qr'    => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(AdvAdvertiser::class, 'advertiser_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(AdvMedia::class, 'campaign_id')->orderBy('sort_order');
    }

    public function survey(): HasOne
    {
        return $this->hasOne(AdvSurvey::class, 'campaign_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvCampaignGroup::class,
            'adv_campaign_group_campaign',
            'campaign_id',
            'campaign_group_id'
        );
    }

    public function playbackLogs(): HasMany
    {
        return $this->hasMany(AdvPlaybackLog::class, 'campaign_id');
    }

    public function qrScans(): HasMany
    {
        return $this->hasMany(AdvQrScan::class, 'campaign_id');
    }

    /** Respuestas de encuesta con email (leads) vía la encuesta de la campaña. */
    public function surveyResponses(): HasManyThrough
    {
        return $this->hasManyThrough(
            AdvSurveyResponse::class,
            AdvSurvey::class,
            'campaign_id',
            'survey_id',
            'id',
            'id'
        );
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeForTablet(Builder $query, AdvTablet $tablet): Builder
    {
        return $query->whereHas('groups', function (Builder $q) use ($tablet) {
            $q->whereHas('tablets', fn (Builder $tq) => $tq->where('adv_tablets.id', $tablet->id));
        });
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->starts_at->lte(now())
            && $this->ends_at->gte(now());
    }

    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }
}
