<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdvCampaignGroup extends Model
{
    protected $table = 'adv_campaign_groups';

    protected $fillable = ['name', 'description'];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function tablets(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvTablet::class,
            'adv_campaign_group_tablet',
            'campaign_group_id',
            'tablet_id'
        );
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(
            AdvCampaign::class,
            'adv_campaign_group_campaign',
            'campaign_group_id',
            'campaign_id'
        );
    }
}
