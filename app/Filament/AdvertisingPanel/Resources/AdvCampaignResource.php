<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource\Pages\CreateAdvCampaign;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource\Pages\EditAdvCampaign;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource\Pages\ListAdvCampaigns;
use App\Models\Advertising\AdvAdvertiser;
use App\Models\Advertising\AdvCampaign;
use App\Models\Advertising\AdvMedia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AdvCampaignResource extends Resource
{
    protected static ?string $model = AdvCampaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Publicidad';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Campaña';
    protected static ?string $pluralLabel = 'Campañas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la campaña')
                    ->schema([
                        Forms\Components\Select::make('advertiser_id')
                            ->label('Anunciante')
                            ->relationship('advertiser', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la campaña')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programada',
                                'active'    => 'Activa',
                                'paused'    => 'Pausada',
                                'expired'   => 'Expirada',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ])->columns(2),

                Forms\Components\Section::make('Vigencia')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Inicio')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin')
                            ->required()
                            ->native(false)
                            ->after('starts_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de Código QR')
                    ->description('Muestra un código QR interactivo durante la reproducción de la campaña.')
                    ->schema([
                        Forms\Components\Toggle::make('has_qr')
                            ->label('Habilitar Código QR')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('qr_url')
                            ->label('URL de destino del QR')
                            ->url()
                            ->required(fn (Forms\Get $get) => $get('has_qr'))
                            ->hidden(fn (Forms\Get $get) => ! $get('has_qr'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Archivos de media')
                    ->schema([
                        Forms\Components\Repeater::make('media')
                            ->relationship()
                            ->schema([
                                // 1. TIPO Y ORDEN
                                Forms\Components\Group::make([
                                    Forms\Components\Select::make('type')
                                        ->label('Tipo de archivo')
                                        ->options(['video' => 'Video', 'image' => 'Imagen'])
                                        ->required()
                                        ->live(),

                                    Forms\Components\TextInput::make('sort_order')
                                        ->label('Orden de reproducción')
                                        ->numeric()
                                        ->default(0),
                                ])->columns(2)->columnSpanFull(),

                                // 2. VISTA PREVIA DEL ARCHIVO EXISTENTE
                                Forms\Components\Placeholder::make('preview')
                                    ->label('Vista previa actual')
                                    ->hidden(fn (?AdvMedia $record) => $record === null || empty($record->cdn_url))
                                    ->content(function (?AdvMedia $record) {
                                        if (! $record || empty($record->cdn_url)) {
                                            return null;
                                        }

                                        if ($record->type === 'video') {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm flex justify-center bg-black">
                                                    <video controls class="max-w-full h-auto" style="max-height: 220px;">
                                                        <source src="'.e($record->cdn_url).'" type="video/mp4">
                                                        <source src="'.e($record->cdn_url).'" type="video/webm">
                                                        Tu navegador no soporta el elemento de video.
                                                    </video>
                                                </div>
                                            ');
                                        }

                                        return new \Illuminate\Support\HtmlString('
                                            <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm flex justify-center bg-gray-50 p-2">
                                                <img src="'.e($record->cdn_url).'" class="max-w-full h-auto object-contain" style="max-height: 220px;" />
                                            </div>
                                        ');
                                    })
                                    ->columnSpanFull(),

                                // 3. SUBIDA DE ARCHIVO (Para nuevos o para reemplazar)
                                Forms\Components\FileUpload::make('storage_path')
                                    ->label(fn (?AdvMedia $record) => $record === null ? 'Subir Archivo' : 'Reemplazar Archivo')
                                    ->disk('r2')
                                    ->directory('campaigns')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'image/jpeg', 'image/png'])
                                    ->required(fn (?AdvMedia $record) => $record === null)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar archivo de media')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['type'] ?? null) === 'video' ? '🎬 Video' : '🖼️ Imagen'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Campaña')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('advertiser.name')
                    ->label('Anunciante')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado DB')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'scheduled',
                        'danger'  => 'paused',
                        'gray'    => 'expired',
                    ]),

                // Estado real: combina status + fechas para detectar campañas expiradas
                // cuyo status en DB no se ha actualizado todavía.
                Tables\Columns\BadgeColumn::make('effective_status')
                    ->label('Estado real')
                    ->getStateUsing(function (AdvCampaign $record): string {
                        if ($record->status !== 'active') {
                            return $record->status;
                        }
                        if ($record->ends_at->isPast()) {
                            return 'vencida';
                        }
                        if ($record->starts_at->isFuture()) {
                            return 'pendiente';
                        }
                        return 'activa';
                    })
                    ->colors([
                        'success' => 'activa',
                        'warning' => 'pendiente',
                        'danger'  => 'vencida',
                        'gray'    => fn ($state) => in_array($state, ['paused', 'expired']),
                    ]),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (AdvCampaign $record) => $record->ends_at->isPast() ? 'danger' : null),

                Tables\Columns\IconColumn::make('has_qr')
                    ->label('QR')
                    ->boolean(),

                Tables\Columns\TextColumn::make('media_count')
                    ->label('Archivos')
                    ->counts('media'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'    => 'Activa',
                        'scheduled' => 'Programada',
                        'paused'    => 'Pausada',
                        'expired'   => 'Expirada',
                    ]),

                Tables\Filters\SelectFilter::make('advertiser_id')
                    ->label('Anunciante')
                    ->relationship('advertiser', 'name'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (AdvCampaign $r) => $r->status === 'active' ? 'Pausar' : 'Activar')
                    ->icon(fn (AdvCampaign $r) => $r->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->action(function (AdvCampaign $record) {
                        $newStatus = $record->status === 'active' ? 'paused' : 'active';
                        $record->update(['status' => $newStatus]);

                        // Invalidar payload cache por tablet (no por tags — los payloads
                        // no se guardan con tags y Cache::tags()->flush() no los borra).
                        $record->load('groups.tablets');
                        $record->groups->each(function ($group) {
                            $group->tablets->each(function ($tablet) {
                                Cache::forget("adv:sync_payload:{$tablet->id}");
                                Cache::put("adv:sync_required:{$tablet->id}", true, now()->addHours(48));
                            });
                        });
                    }),

                Tables\Actions\Action::make('view_qr')
                    ->label('Ver QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->hidden(fn (AdvCampaign $record) => ! $record->has_qr)
                    ->modalHeading(fn (AdvCampaign $record) => 'Código QR: ' . $record->name)
                    ->modalContent(function (AdvCampaign $record) {
                        $trackingUrl = url('/api/adv/campaigns/' . $record->id . '/qr');
                        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($trackingUrl);

                        return new \Illuminate\Support\HtmlString('
                            <div class="flex flex-col items-center justify-center space-y-4 py-4">
                                <img src="' . $qrImageUrl . '" alt="QR Code" class="w-64 h-64 border rounded shadow-sm" />
                                <p class="text-sm text-gray-500 text-center break-all px-4">URL de rastreo: <br>' . e($trackingUrl) . '</p>
                                <div class="flex space-x-2">
                                    <a href="' . e($qrImageUrl) . '" target="_blank" download="QR_Campana_' . (int) $record->id . '.png" style="background-color: #f59e0b; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; font-weight: 500;">
                                        Abrir Imagen para Descargar
                                    </a>
                                </div>
                            </div>
                        ');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdvCampaigns::route('/'),
            'create' => CreateAdvCampaign::route('/create'),
            'edit'   => EditAdvCampaign::route('/{record}/edit'),
        ];
    }
}
