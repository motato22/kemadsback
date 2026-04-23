<?php

namespace App\Exports;

use App\Models\Advertising\AdvSurveyResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SurveyLeadsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected int $surveyId
    ) {}

    public function query()
    {
        return AdvSurveyResponse::query()
            ->where('survey_id', $this->surveyId)
            ->whereNotNull('email')
            ->with(['tablet', 'survey']);
    }

    public function headings(): array
    {
        return [
            'Email del Pasajero',
            'ID de Unidad (Tablet)',
            'Fecha de Captura',
            'Acertó (Trivia)',
        ];
    }

    public function map($response): array
    {
        $acerto = '—';
        if ($response->survey && $response->survey->type === 'trivia') {
            $total = DB::table('adv_survey_response_answers')
                ->where('response_id', $response->id)
                ->count();
            $correct = DB::table('adv_survey_response_answers')
                ->where('response_id', $response->id)
                ->where('is_correct', true)
                ->count();
            $acerto = $total > 0 ? "{$correct}/{$total}" : '—';
        }

        return [
            $response->email,
            $response->tablet?->unit_id ?? 'N/A',
            $response->completed_at?->format('d/m/Y H:i') ?? '—',
            $acerto,
        ];
    }
}
