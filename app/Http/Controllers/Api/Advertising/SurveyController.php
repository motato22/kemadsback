<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvSurveyResult;
use App\Models\Advertising\AdvTablet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    /**
     * POST /api/adv/survey-results
     *
     * Registra las respuestas a encuestas/trivia enviadas por la tablet.
     * Al igual que playback-events, soporta lotes de resultados acumulados offline.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $validated = $request->validate([
            'results'                          => ['required', 'array', 'min:1', 'max:100'],
            'results.*.survey_id'              => ['required', 'integer', 'exists:adv_surveys,id'],
            'results.*.question_id'            => ['required', 'integer', 'exists:adv_survey_questions,id'],
            'results.*.driver_shift_id'        => ['nullable', 'integer', 'exists:adv_driver_shifts,id'],
            'results.*.selected_option_index'  => ['required', 'integer', 'min:0'],
            'results.*.completion_status'      => ['required', Rule::in(['shown', 'started', 'completed'])],
            'results.*.answered_at'            => ['required', 'date'],
        ]);

        $records = collect($validated['results'])->map(fn ($result) => [
            'survey_id'             => $result['survey_id'],
            'question_id'           => $result['question_id'],
            'tablet_id'             => $tablet->id,
            'driver_shift_id'       => $result['driver_shift_id'] ?? null,
            'selected_option_index' => $result['selected_option_index'],
            'is_correct'            => null, // Se evalúa en el servidor si es trivia
            'completion_status'     => $result['completion_status'],
            'answered_at'           => $result['answered_at'],
        ])->all();

        AdvSurveyResult::insert($records);

        return response()->json([
            'status'   => 'ok',
            'recorded' => count($records),
        ]);
    }
}
