<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvQuestion extends Model
{
    protected $table = 'adv_questions';

    protected $fillable = [
        'survey_id',
        'question_text',
        'type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(AdvSurvey::class, 'survey_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(AdvOption::class, 'question_id');
    }
}
