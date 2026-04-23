<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvSurveyResponse extends Model
{
    protected $table = 'adv_survey_responses';

    public $timestamps = false;

    protected $fillable = [
        'survey_id',
        'tablet_id',
        'email',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->completed_at)) {
                $model->completed_at = now();
            }
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(AdvSurvey::class, 'survey_id');
    }

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }
}
