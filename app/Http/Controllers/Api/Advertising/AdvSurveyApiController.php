<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvCampaign;
use App\Models\Advertising\AdvOption;
use App\Models\Advertising\AdvSurvey;
use App\Models\Advertising\AdvSurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvSurveyApiController extends Controller
{
    /**
     * GET: Encuesta activa para una campaña.
     * La APK Reproductora llama esto al descargar el contenido de la campaña.
     * Incluye preguntas ordenadas y opciones (con is_correct para trivia).
     */
    public function show(AdvCampaign $campaign): JsonResponse
    {
        $survey = AdvSurvey::where('campaign_id', $campaign->id)
            ->where('is_active', true)
            ->with([
                'questions' => fn ($q) => $q->orderBy('sort_order'),
                'questions.options',
            ])
            ->first();

        if (! $survey) {
            return response()->json(['message' => 'No active survey for this campaign'], 404);
        }

        return response()->json(['data' => $survey]);
    }

    /**
     * POST: Respuestas del pasajero y lead (email).
     * La tableta envía cuando tiene red (o al acumular offline).
     */
    public function storeResponse(Request $request, AdvSurvey $survey): JsonResponse
    {
        $validated = $request->validate([
            'tablet_id' => 'required|exists:adv_tablets,id',
            'email'     => 'nullable|email|max:120',
            'answers'   => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:adv_questions,id',
            'answers.*.option_id'   => 'required|integer|exists:adv_options,id',
        ]);

        DB::beginTransaction();

        try {
            $response = AdvSurveyResponse::create([
                'survey_id' => $survey->id,
                'tablet_id' => $validated['tablet_id'],
                'email'     => $validated['email'] ?? null,
            ]);

            $optionIds = collect($validated['answers'])->pluck('option_id')->unique()->values()->all();
            $optionsMap = AdvOption::whereIn('id', $optionIds)->pluck('is_correct', 'id');

            $answersData = [];
            foreach ($validated['answers'] as $answer) {
                $answersData[] = [
                    'response_id' => $response->id,
                    'question_id' => $answer['question_id'],
                    'option_id'   => $answer['option_id'],
                    'is_correct'  => $survey->type === 'trivia' ? ($optionsMap[$answer['option_id']] ?? null) : null,
                ];
            }

            DB::table('adv_survey_response_answers')->insert($answersData);

            DB::commit();

            return response()->json([
                'message'     => 'Response saved successfully',
                'response_id' => $response->id,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error saving response',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
