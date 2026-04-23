<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AdvAdvertiser extends Model
{
    protected $table = 'adv_advertisers';

    protected $fillable = [
        'name',
        'rfc',
        'contact_name',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function campaigns(): HasMany
    {
        return $this->hasMany(AdvCampaign::class, 'advertiser_id');
    }

    public function activeCampaigns(): HasMany
    {
        return $this->campaigns()->where('status', 'active');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
