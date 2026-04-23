<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Exports\MonthlyCampaignReportExport;
use App\Filament\AdvertisingPanel\Resources\AdvReportResource\Pages\ListAdvReports;
use App\Models\Advertising\AdvCampaign;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Recurso de solo lectura: generador de estados de cuenta publicitarios.
 * No CRUD: interfaz de consulta con filtros por anunciante y rango de fechas.
 */
class AdvReportResource extends Resource
{
    protected static ?string $model = AdvCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Publicidad';
    protected static ?int $navigationSort = 10;
    protected static ?string $label = 'Reporte mensual';
    protected static ?string $pluralLabel = 'Reportes mensuales';
    protected static ?string $slug = 'reportes';

    public static function getPages(): array
    {
        return [
            'index' => ListAdvReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('advertiser.name')
                    ->label('Anunciante')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Campaña')
                    ->searchable(),

                Tables\Columns\TextColumn::make('playback_logs_count')
                    ->label('Impresiones')
                    ->numeric()
                    ->default(0),

                Tables\Columns\TextColumn::make('total_duration_seconds')
                    ->label('Tiempo en pantalla')
                    ->formatStateUsing(fn ($state) => $state
                        ? number_format((float) $state / 60, 2) . ' min'
                        : '0 min'),

                Tables\Columns\TextColumn::make('qr_scans_count')
                    ->label('Escaneos QR')
                    ->numeric()
                    ->default(0),

                Tables\Columns\TextColumn::make('survey_responses_count')
                    ->label('Leads')
                    ->numeric()
                    ->default(0),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('advertiser_id')
                    ->label('Anunciante')
                    ->relationship('advertiser', 'name')
                    ->searchable(),

                Tables\Filters\Filter::make('rango_fechas')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->helperText('Rango opcional. Aplicar para cargar impresiones, tiempo, QR y leads.'),
                        \Filament\Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->indicateUsing(fn (array $data): ?string => ($data['desde'] ?? $data['hasta'])
                        ? 'Período: ' . ($data['desde'] ?? '—') . ' a ' . ($data['hasta'] ?? '—')
                        : 'Todo el período')
                    ->query(function (Builder $query, array $data): Builder {
                        $desde = $data['desde'] ?? null;
                        $hasta = $data['hasta'] ?? null;
                        $desdeStart = $desde ? $desde . ' 00:00:00' : null;
                        $hastaEnd = $hasta ? $hasta . ' 23:59:59' : null;

                        $query->withCount([
                            'playbackLogs as playback_logs_count' => function (Builder $q) use ($desdeStart, $hastaEnd) {
                                if ($desdeStart) {
                                    $q->where('started_at', '>=', $desdeStart);
                                }
                                if ($hastaEnd) {
                                    $q->where('started_at', '<=', $hastaEnd);
                                }
                            },
                        ])->withSum([
                            'playbackLogs as total_duration_seconds' => function (Builder $q) use ($desdeStart, $hastaEnd) {
                                if ($desdeStart) {
                                    $q->where('started_at', '>=', $desdeStart);
                                }
                                if ($hastaEnd) {
                                    $q->where('started_at', '<=', $hastaEnd);
                                }
                            },
                        ], 'duration_seconds')
                            ->withCount([
                                'qrScans as qr_scans_count' => function (Builder $q) use ($desdeStart, $hastaEnd) {
                                    if ($desdeStart) {
                                        $q->where('scanned_at', '>=', $desdeStart);
                                    }
                                    if ($hastaEnd) {
                                        $q->where('scanned_at', '<=', $hastaEnd);
                                    }
                                },
                            ])->withCount([
                                'surveyResponses as survey_responses_count' => function (Builder $q) use ($desdeStart, $hastaEnd) {
                                    $q->whereNotNull('email');
                                    if ($desdeStart) {
                                        $q->where('completed_at', '>=', $desdeStart);
                                    }
                                    if ($hastaEnd) {
                                        $q->where('completed_at', '<=', $hastaEnd);
                                    }
                                },
                            ]);

                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('export_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (AdvCampaign $record) {
                        $fileName = 'Reporte_' . \Str::slug($record->name) . '_' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(
                            new MonthlyCampaignReportExport($record->id),
                            $fileName
                        );
                    }),

                // PDF: integrar DomPDF + vista con branding KEM cuando se requiera
            ])
            ->bulkActions([])
            ->defaultSort('advertiser.name');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('advertiser');
    }
}
