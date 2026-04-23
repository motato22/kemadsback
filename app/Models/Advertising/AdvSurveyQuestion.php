<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvSurveyQuestion extends Model
{
    protected $table = 'adv_survey_questions';

    protected $fillable = [
        'survey_id',
        'question_text',
        'options',
        'correct_option_index',
        'sort_order',
    ];

    protected $casts = [
        'options'              => 'array',
        'correct_option_index' => 'integer',
        'sort_order'           => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(AdvSurvey::class, 'survey_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(AdvSurveyResult::class, 'question_id');
    }
}
