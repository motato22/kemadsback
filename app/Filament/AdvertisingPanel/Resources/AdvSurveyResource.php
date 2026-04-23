<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Exports\SurveyLeadsExport;
use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages\CreateAdvSurvey;
use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages\EditAdvSurvey;
use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages\ListAdvSurveys;
use App\Models\Advertising\AdvSurvey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class AdvSurveyResource extends Resource
{
    protected static ?string $model = AdvSurvey::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Publicidad';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Encuesta';
    protected static ?string $pluralLabel = 'Encuestas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de la Encuesta/Trivia')
                    ->schema([
                        Forms\Components\Select::make('campaign_id')
                            ->label('Campaña Asociada')
                            ->relationship(
                                name: 'campaign',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereIn('status', ['active', 'scheduled'])
                            )
                            ->required()
                            ->searchable()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre interno')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de dinámica')
                            ->options([
                                'survey' => 'Encuesta (Opinión/Datos)',
                                'trivia' => 'Trivia (Respuestas correctas)',
                            ])
                            ->default('survey')
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('timeout_seconds')
                            ->label('Tiempo de inactividad (Timeout)')
                            ->numeric()
                            ->default(30)
                            ->suffix('segundos')
                            ->helperText('Si el pasajero no toca la pantalla en este tiempo, regresan los anuncios.'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Preguntas')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship()
                            ->label('')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? null)
                            ->orderColumn('sort_order')
                            ->schema([
                                Forms\Components\TextInput::make('question_text')
                                    ->label('Pregunta')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('type')
                                    ->label('Tipo de respuesta')
                                    ->options([
                                        'single_choice' => 'Opción Única',
                                        'multiple_choice' => 'Opción Múltiple',
                                    ])
                                    ->default('single_choice')
                                    ->required()
                                    ->helperText('Opción única: el pasajero elige UNA entre varias. Opción múltiple: puede marcar varias. En ambos casos añade abajo las alternativas (A, B, C…).'),

                                Forms\Components\Repeater::make('options')
                                    ->relationship()
                                    ->label('Opciones de respuesta')
                                    ->addActionLabel('Agregar opción')
                                    ->helperText('Lista de alternativas entre las que el pasajero elegirá (una o varias según el tipo de pregunta).')
                                    ->schema([
                                        Forms\Components\TextInput::make('option_text')
                                            ->label('Texto de la opción')
                                            ->required(),
                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('¿Es la respuesta correcta?')
                                            ->default(false)
                                            ->visible(function (\Filament\Forms\Components\Toggle $component) {
                                                $data = $component->getLivewire()->data ?? [];
                                                return ($data['type'] ?? 'survey') === 'trivia';
                                            }),
                                    ])->columns(2),
                            ])
                            ->addActionLabel('Agregar Nueva Pregunta'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('campaign.name')
                    ->label('Campaña'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'trivia' ? 'Trivia' : 'Encuesta'),

                Tables\Columns\TextColumn::make('timeout_seconds')
                    ->label('Timeout (seg)')
                    ->suffix(' s'),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Preguntas')
                    ->counts('questions'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->actions([
                Action::make('download_leads')
                    ->label('Descargar Leads')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (AdvSurvey $record) {
                        $fileName = 'Leads_' . str_replace(' ', '_', $record->name) . '_' . now()->format('Y-m-d') . '.xlsx';
                        return Excel::download(
                            new SurveyLeadsExport($record->id),
                            $fileName
                        );
                    })
                    ->visible(fn (AdvSurvey $record): bool => $record->responses()->whereNotNull('email')->exists()),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-device-tablet')
                    ->color('info')
                    ->modalHeading('Simulador de Tableta')
                    ->modalDescription('Así se verá la dinámica en las pantallas de los vehículos.')
                    ->modalContent(function (AdvSurvey $record) {
                        $record->load(['questions.options']);
                        return view('components.survey-preview', ['survey' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar simulador')
                    ->modalWidth('7xl'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdvSurveys::route('/'),
            'create' => CreateAdvSurvey::route('/create'),
            'edit'   => EditAdvSurvey::route('/{record}/edit'),
        ];
    }
}
