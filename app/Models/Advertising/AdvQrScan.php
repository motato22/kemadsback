<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvQrScan extends Model
{
    protected $table = 'adv_qr_scans';

    protected $fillable = [
        'tablet_id',
        'campaign_id',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdvCampaign::class, 'campaign_id');
    }
}
