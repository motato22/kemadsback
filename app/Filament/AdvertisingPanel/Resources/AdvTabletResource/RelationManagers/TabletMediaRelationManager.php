<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvTabletResource\RelationManagers;

use App\Models\Advertising\AdvTabletMedia;
use App\Services\Advertising\TabletCommandService;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class TabletMediaRelationManager extends RelationManager
{
    protected static string $relationship = 'tabletMedia';
    protected static ?string $title       = 'Media descargada';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('media.id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('media.type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'video' => 'info',
                        'image' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('media.campaign.name')
                    ->label('Campaña')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'ready',
                        'warning' => 'downloading',
                        'danger'  => 'failed',
                    ])
                    ->icons([
                        'heroicon-o-check-circle'    => 'ready',
                        'heroicon-o-arrow-down-tray' => 'downloading',
                        'heroicon-o-x-circle'        => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('file_size_kb')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) =>
                        $state ? number_format($state / 1024, 1) . ' MB' : '—'
                    ),

                Tables\Columns\TextColumn::make('downloaded_at')
                    ->label('Descargado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Pendiente'),
            ])
            ->defaultSort('downloaded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ready'       => 'Listo',
                        'downloading' => 'Descargando',
                        'failed'      => 'Fallido',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('retry')
                    ->label('Reintentar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (AdvTabletMedia $record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->modalHeading('Reintentar descarga')
                    ->modalDescription(fn (AdvTabletMedia $record) =>
                        "Se enviará un comando a la tablet en el próximo heartbeat para reintentar la descarga del media #{$record->media_id}."
                    )
                    ->action(function (AdvTabletMedia $record) {
                        $tablet = $this->getOwnerRecord();

                        $record->update([
                            'status'        => 'downloading',
                            'downloaded_at' => null,
                        ]);

                        TabletCommandService::push($tablet, [
                            'type'     => 'retry_media',
                            'media_id' => $record->media_id,
                        ]);

                        Cache::put(
                            "adv:sync_required:{$tablet->id}",
                            true,
                            now()->addHours(48)
                        );

                        Notification::make()
                            ->title('Comando encolado')
                            ->body('La tablet recibirá el comando en el próximo heartbeat (~3 min).')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('markReady')
                    ->label('Marcar como listo')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (AdvTabletMedia $record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->action(function (AdvTabletMedia $record) {
                        $record->update([
                            'status'        => 'ready',
                            'downloaded_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Estado actualizado')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('retryFailed')
                    ->label('Reintentar seleccionados')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $tablet = $this->getOwnerRecord();

                        foreach ($records as $record) {
                            if ($record->status === 'failed') {
                                $record->update([
                                    'status'        => 'downloading',
                                    'downloaded_at' => null,
                                ]);

                                TabletCommandService::push($tablet, [
                                    'type'     => 'retry_media',
                                    'media_id' => $record->media_id,
                                ]);
                            }
                        }

                        Cache::put(
                            "adv:sync_required:{$tablet->id}",
                            true,
                            now()->addHours(48)
                        );

                        Notification::make()
                            ->title('Comandos encolados')
                            ->body('La tablet recibirá los comandos en el próximo heartbeat.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}

