<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Filament\AdvertisingPanel\Pages\ManageGuardianApk;
use App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages\CreateAdvTablet;
use App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages\EditAdvTablet;
use App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages\ListAdvTablets;
use App\Filament\AdvertisingPanel\Resources\AdvTabletResource\RelationManagers\TabletMediaRelationManager;
use App\Models\Advertising\AdvCampaignGroup;
use App\Models\Advertising\AdvTablet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification as FilamentNotification;

class AdvTabletResource extends Resource
{
    protected static ?string $model = AdvTablet::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-tablet';
    protected static ?string $navigationGroup = 'Dispositivos';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Tablet';
    protected static ?string $pluralLabel = 'Tablets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del dispositivo')
                    ->schema([
                        Forms\Components\TextInput::make('unit_id')
                            ->label('Número de Unidad')
                            ->required()
                            ->maxLength(32)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre descriptivo')
                            ->maxLength(80)
                            ->placeholder('Ej: Unidad 12 - Ruta Norte'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'provisioning' => 'Aprovisionando',
                                'active'       => 'Activa',
                                'inactive'     => 'Inactiva',
                            ])
                            ->required()
                            ->default('provisioning'),

                        Forms\Components\TextInput::make('device_id')
                            ->label('Device ID (Android)')
                            ->maxLength(64)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Se asigna automáticamente durante el aprovisionamiento.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_id')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'provisioning',
                        'danger'  => 'inactive',
                    ]),

                Tables\Columns\IconColumn::make('online')
                    ->label('Online')
                    ->boolean()
                    ->getStateUsing(fn (AdvTablet $record) => $record->isOnline()),

                Tables\Columns\TextColumn::make('app_version')
                    ->label('Versión App'),

                Tables\Columns\TextColumn::make('battery_level')
                    ->label('Batería')
                    ->getStateUsing(fn (AdvTablet $record) =>
                        $record->battery_level !== null ? $record->battery_level . '%' : '—'
                    )
                    ->color(fn (AdvTablet $record) => match(true) {
                        $record->battery_level === null      => null,
                        $record->battery_level <= 15        => 'danger',
                        $record->battery_level <= 30        => 'warning',
                        default                             => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('storage_used')
                    ->label('Storage')
                    ->getStateUsing(fn (AdvTablet $record) =>
                        number_format($record->storageUsedKb() / 1024 / 1024, 1) . ' GB'
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('media_failed_count')
                    ->label('Media fallida')
                    ->getStateUsing(fn (AdvTablet $record) =>
                        $record->tabletMedia()->where('status', 'failed')->count()
                    )
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Último heartbeat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'       => 'Activa',
                        'inactive'     => 'Inactiva',
                        'provisioning' => 'Aprovisionando',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('viewProvisionQr')
                    ->label('Ver QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->visible(fn (AdvTablet $record) => $record->status === 'provisioning')
                    ->modalHeading(fn (AdvTablet $record) => 'QR de aprovisionamiento — ' . $record->unit_id)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (AdvTablet $record) {
                        $appUrl       = rtrim(config('advertising.public_app_url', config('app.url')), '/');
                        $guardianInfo = ManageGuardianApk::getGuardianInfo();

                        $secret = Cache::get("adv:provision_secret:{$record->unit_id}");
                        if (! $secret) {
                            $secret = bin2hex(random_bytes(32));
                            Cache::put("adv:provision_secret:{$record->unit_id}", $secret, now()->addHours(24));
                        }

                        $androidPayload = [
                            'android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME'
                                => 'com.kemadvertising.guardiana.debug/com.kemadvertising.guardiana.data.receiver.GuardianaAdminReceiver',
                            'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION'
                                => $appUrl . '/api/adv/apk/guardian',
                            'android.app.extra.PROVISIONING_SKIP_ENCRYPTION' => true,
                            'android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE' => [
                                'server_url'       => $appUrl,
                                'unit_id'          => $record->unit_id,
                                'provision_secret' => $secret,
                            ],
                        ];

                        if ($guardianInfo && ! empty($guardianInfo['cert_checksum_b64'])) {
                            $androidPayload['android.app.extra.PROVISIONING_DEVICE_ADMIN_SIGNATURE_CHECKSUM'] = $guardianInfo['cert_checksum_b64'];
                        }

                        if ($guardianInfo && ! empty($guardianInfo['wifi_ssid'])) {
                            $androidPayload['android.app.extra.PROVISIONING_WIFI_SSID']          = $guardianInfo['wifi_ssid'];
                            $androidPayload['android.app.extra.PROVISIONING_WIFI_SECURITY_TYPE'] = $guardianInfo['wifi_security'] ?? 'WPA';
                            if (! empty($guardianInfo['wifi_password'])) {
                                $androidPayload['android.app.extra.PROVISIONING_WIFI_PASSWORD'] = $guardianInfo['wifi_password'];
                            }
                        }

                        $payloadJson   = json_encode($androidPayload);
                        $qrUrl         = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&margin=10&data=' . urlencode($payloadJson);
                        $qrDownloadUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=800x800&margin=20&format=png&data=' . urlencode($payloadJson);

                        $missingApk = empty($guardianInfo);

                        return view('filament.advertising.modals.provision-qr', [
                            'qrUrl'             => $qrUrl,
                            'serverUrl'         => $appUrl,
                            'unitId'            => $record->unit_id,
                            'provisionSecret'   => $secret,
                            'qrDownloadUrl'     => $qrDownloadUrl,
                            'missingApk'        => $missingApk,
                            'apkVersion'        => $guardianInfo ? ($guardianInfo['version'] ?? null) : null,
                            'wifiSsid'          => $guardianInfo ? ($guardianInfo['wifi_ssid'] ?? null) : null,
                            'certChecksum'      => $guardianInfo ? ($guardianInfo['cert_checksum_b64'] ?? null) : null,
                        ]);
                    })
                    ->modalWidth('2xl'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign_to_group')
                        ->label('Asignar a Grupo')
                        ->icon('heroicon-o-rectangle-group')
                        ->form([
                            Forms\Components\Select::make('campaign_group_id')
                                ->label('Selecciona el Grupo destino')
                                ->options(AdvCampaignGroup::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $group = AdvCampaignGroup::find($data['campaign_group_id']);
                            if (! $group) {
                                return;
                            }
                            foreach ($records as $tablet) {
                                $tablet->campaignGroups()->sync([$group->id]);
                            }
                            Cache::tags(['adv:sync'])->flush();
                            FilamentNotification::make()
                                ->title('Tablets asignadas')
                                ->body(count($records) . ' tablet(s) asignadas al grupo "' . $group->name . '".')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('viewBulkProvisionQr')
                        ->label('Ver QR del lote')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->modalHeading('QRs de aprovisionamiento — Lote')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->modalContent(function (Collection $records) {
                            $appUrl        = rtrim(config('advertising.public_app_url', config('app.url')), '/');
                            $guardianInfo  = ManageGuardianApk::getGuardianInfo();
                            $provisionable = $records->where('status', 'provisioning')->values();

                            if ($provisionable->isEmpty()) {
                                FilamentNotification::make()
                                    ->title('Sin tablets válidas')
                                    ->body('Ninguna de las tablets seleccionadas está en estado "Aprovisionando".')
                                    ->danger()
                                    ->send();

                                return view('filament.advertising.modals.provision-qr-bulk', ['items' => []]);
                            }

                            $certChecksum = ($guardianInfo && ! empty($guardianInfo['cert_checksum_b64']))
                                ? $guardianInfo['cert_checksum_b64']
                                : null;

                            $items = $provisionable->map(function (AdvTablet $tablet) use ($appUrl, $guardianInfo, $certChecksum) {
                                $secret = Cache::get("adv:provision_secret:{$tablet->unit_id}");
                                if (! $secret) {
                                    $secret = bin2hex(random_bytes(32));
                                    Cache::put("adv:provision_secret:{$tablet->unit_id}", $secret, now()->addHours(24));
                                }

                                $androidPayload = [
                                    'android.app.extra.PROVISIONING_DEVICE_ADMIN_COMPONENT_NAME'
                                        => 'com.kemadvertising.guardiana.debug/com.kemadvertising.guardiana.data.receiver.GuardianaAdminReceiver',
                                    'android.app.extra.PROVISIONING_DEVICE_ADMIN_PACKAGE_DOWNLOAD_LOCATION'
                                        => $appUrl . '/api/adv/apk/guardian',
                                    'android.app.extra.PROVISIONING_SKIP_ENCRYPTION' => true,
                                    'android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE' => [
                                        'server_url'       => $appUrl,
                                        'unit_id'          => $tablet->unit_id,
                                        'provision_secret' => $secret,
                                    ],
                                ];

                                if ($certChecksum) {
                                    $androidPayload['android.app.extra.PROVISIONING_DEVICE_ADMIN_SIGNATURE_CHECKSUM'] = $certChecksum;
                                }

                                if ($guardianInfo && ! empty($guardianInfo['wifi_ssid'])) {
                                    $androidPayload['android.app.extra.PROVISIONING_WIFI_SSID']          = $guardianInfo['wifi_ssid'];
                                    $androidPayload['android.app.extra.PROVISIONING_WIFI_SECURITY_TYPE'] = $guardianInfo['wifi_security'] ?? 'WPA';
                                    if (! empty($guardianInfo['wifi_password'] ?? null)) {
                                        $androidPayload['android.app.extra.PROVISIONING_WIFI_PASSWORD'] = $guardianInfo['wifi_password'];
                                    }
                                }

                                $payloadJson = json_encode($androidPayload);

                                return [
                                    'unitId'          => $tablet->unit_id,
                                    'serverUrl'       => $appUrl,
                                    'provisionSecret' => $secret,
                                    'qrUrl'           => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=10&data=' . urlencode($payloadJson),
                                    'qrDownloadUrl'   => 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=20&format=png&data=' . urlencode($payloadJson),
                                ];
                            })->all();

                            return view('filament.advertising.modals.provision-qr-bulk', ['items' => $items]);
                        })
                        ->modalWidth('4xl')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TabletMediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdvTablets::route('/'),
            'create' => CreateAdvTablet::route('/create'),
            'edit'   => EditAdvTablet::route('/{record}/edit'),
        ];
    }
}
