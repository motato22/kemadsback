<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvSurveyResult extends Model
{
    public $timestamps = false;

    protected $table = 'adv_survey_results';

    protected $fillable = [
        'survey_id',
        'question_id',
        'tablet_id',
        'driver_shift_id',
        'selected_option_index',
        'is_correct',
        'completion_status',
        'answered_at',
    ];

    protected $casts = [
        'selected_option_index' => 'integer',
        'is_correct'            => 'boolean',
        'answered_at'           => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function survey(): BelongsTo
    {
        return $this->belongsTo(AdvSurvey::class, 'survey_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AdvSurveyQuestion::class, 'question_id');
    }

    public function tablet(): BelongsTo
    {
        return $this->belongsTo(AdvTablet::class, 'tablet_id');
    }

    public function driverShift(): BelongsTo
    {
        return $this->belongsTo(AdvDriverShift::class, 'driver_shift_id');
    }
}
