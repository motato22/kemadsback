<?php

namespace App\Http\Requests\Advertising;

use Illuminate\Foundation\Http\FormRequest;

class PlaybackEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'events' => ['required', 'array', 'min:1', 'max:500'],
            'events.*.campaign_id' => ['required', 'integer', 'exists:adv_campaigns,id'],
            'events.*.started_at' => ['required', 'date'],
            'events.*.ended_at' => ['nullable', 'date'],
            'events.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
