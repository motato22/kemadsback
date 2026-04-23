<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvSurvey extends Model
{
    protected $table = 'adv_surveys';

    protected $fillable = [
        'campaign_id',
        'name',
        'type',
        'timeout_seconds',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'timeout_seconds' => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvCampaign::class, 'campaign_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AdvQuestion::class, 'survey_id')->orderBy('sort_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(AdvSurveyResult::class, 'survey_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AdvSurveyResponse::class, 'survey_id');
    }
}
