<?php

namespace App\Filament\AdvertisingPanel\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ManageGuardianApk extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'APK Guardiana';
    protected static ?string $navigationGroup = 'Dispositivos';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.advertising.pages.manage-guardian-apk';

    // Formulario A — solo configuración (sin APK)
    public ?array $configData = [];

    // Formulario B — subir APK nuevo
    public ?array $uploadData = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->can('page_ManageGuardianApk'));
    }

    public static function getGuardianInfo(): ?array
    {
        $path = storage_path('app/apk/guardian-info.json');
        if (! file_exists($path)) {
            return null;
        }
        $decoded = json_decode(file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    public function getPhpUploadLimits(): array
    {
        $toBytes = fn (string $v): int => (int) preg_replace_callback('/^(\d+)([KMG])?$/i', function ($m) {
            $n = (int) $m[1];
            return match (strtoupper($m[2] ?? '')) {
                'K' => $n * 1024,
                'M' => $n * 1024 * 1024,
                'G' => $n * 1024 * 1024 * 1024,
                default => $n,
            };
        }, trim($v));

        $uploadMax = ini_get('upload_max_filesize');
        $postMax   = ini_get('post_max_size');

        return [
            'upload_max_filesize' => $uploadMax,
            'post_max_size'       => $postMax,
            'ok_for_apk'          => $toBytes($uploadMax) >= 15 * 1024 * 1024 && $toBytes($postMax) >= 15 * 1024 * 1024,
        ];
    }

    public function mount(): void
    {
        $info = self::getGuardianInfo() ?? [];

        $this->configForm->fill([
            'cert_checksum_b64' => $info['cert_checksum_b64'] ?? '',
            'wifi_ssid'         => $info['wifi_ssid'] ?? '',
            'wifi_password'     => $info['wifi_password'] ?? '',
            'wifi_security'     => $info['wifi_security'] ?? 'WPA',
        ]);

        $this->uploadForm->fill([
            'version'           => $info['version'] ?? '',
            'cert_checksum_b64' => $info['cert_checksum_b64'] ?? '',
        ]);
    }

    // ── Formulario A: solo configuración ─────────────────────────────────────

    protected function getForms(): array
    {
        return ['configForm', 'uploadForm'];
    }

    public function configForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Checksum del certificado')
                    ->description('El checksum del certificado de firma del APK es necesario para que Android valide la autenticidad durante el aprovisionamiento por QR. Cámbialo aquí sin necesidad de subir una nueva versión del APK.')
                    ->schema([
                        TextInput::make('cert_checksum_b64')
                            ->label('Checksum del certificado (Base64 URL-safe)')
                            ->placeholder('Ej: NZxuParBhPIa8Hro8FgwdGMD9fl4FKCrxkvuNa0sd2A')
                            ->helperText('Obtener con: apksigner verify --print-certs app.apk | grep SHA-256 → luego convertir el hex con: echo -n "<hex>" | xxd -r -p | base64 | tr \'+/\' \'-_\' | tr -d \'=\'')
                            ->maxLength(64)
                            ->columnSpanFull(),
                    ]),

                Section::make('Configuración WiFi para aprovisionamiento')
                    ->description('Estas credenciales se incluyen en el QR para que la tablet se conecte automáticamente durante el setup de Android. Puedes actualizarlas en cualquier momento sin subir un APK nuevo.')
                    ->schema([
                        TextInput::make('wifi_ssid')
                            ->label('SSID (nombre de la red)')
                            ->placeholder('Ej: KEM-Oficina')
                            ->maxLength(64),

                        TextInput::make('wifi_password')
                            ->label('Contraseña WiFi')
                            ->password()
                            ->revealable()
                            ->maxLength(128),

                        Select::make('wifi_security')
                            ->label('Tipo de seguridad')
                            ->options([
                                'WPA'  => 'WPA / WPA2',
                                'WEP'  => 'WEP',
                                'NONE' => 'Sin contraseña',
                            ])
                            ->default('WPA'),
                    ])
                    ->columns(3),
            ])
            ->statePath('configData');
    }

    public function saveConfig(): void
    {
        $data = $this->configForm->getState();
        $info = self::getGuardianInfo() ?? [];

        $info['cert_checksum_b64'] = $data['cert_checksum_b64'] ?? '';
        $info['wifi_ssid']         = $data['wifi_ssid'] ?? '';
        $info['wifi_password']     = $data['wifi_password'] ?? '';
        $info['wifi_security']     = $data['wifi_security'] ?? 'WPA';

        file_put_contents(storage_path('app/apk/guardian-info.json'), json_encode($info, JSON_PRETTY_PRINT));

        Notification::make()
            ->success()
            ->title('Configuración guardada')
            ->body('Checksum y WiFi actualizados. Los nuevos QRs usarán estos valores.')
            ->send();
    }

    // ── Formulario B: subir APK nuevo ─────────────────────────────────────────

    public function uploadForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Subir nueva versión del APK Guardiana')
                    ->description('La Guardiana es el Device Owner — solo se actualiza manualmente desde aquí, nunca vía OTA. Al subir una nueva versión, actualiza también el checksum del certificado si cambió el keystore.')
                    ->schema([
                        TextInput::make('version')
                            ->label('Versión (ej. 1.0.1-debug)')
                            ->required(),

                        TextInput::make('cert_checksum_b64')
                            ->label('Checksum del certificado (Base64 URL-safe)')
                            ->placeholder('Déjalo vacío para conservar el checksum actual')
                            ->helperText('Solo actualiza si firmaste con un keystore diferente.')
                            ->maxLength(64),

                        FileUpload::make('apk_file')
                            ->label('Archivo APK')
                            ->acceptedFileTypes([
                                'application/vnd.android.package-archive',
                                'application/octet-stream',
                                'application/zip',
                                'application/x-zip-compressed',
                                'application/x-zip',
                                'application/x-apk',
                            ])
                            ->disk('public')
                            ->directory('apk/guardian-temp')
                            ->maxSize(50 * 1024)
                            ->helperText('Máximo 50 MB.')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('uploadData');
    }

    public function uploadApk(): void
    {
        $data      = $this->uploadForm->getState();
        $tempPath  = $data['apk_file'];
        $finalPath = 'apk/guardian-latest.apk';

        $privateDir = storage_path('app/apk');
        if (! is_dir($privateDir)) {
            mkdir($privateDir, 0755, true);
        }

        $content = Storage::disk('public')->get($tempPath);
        Storage::disk('local')->put($finalPath, $content);
        Storage::disk('public')->delete($tempPath);

        $absolutePath = Storage::disk('local')->path($finalPath);
        $sha256hex    = hash_file('sha256', $absolutePath);
        $appUrl       = rtrim(config('advertising.public_app_url', config('app.url')), '/');
        $url          = $appUrl . '/api/adv/apk/guardian';

        // Preservar cert_checksum_b64 y WiFi actuales si no se proporcionó uno nuevo
        $existing          = self::getGuardianInfo() ?? [];
        $certChecksum      = ! empty($data['cert_checksum_b64'])
            ? $data['cert_checksum_b64']
            : ($existing['cert_checksum_b64'] ?? '');

        $info = [
            'version'           => $data['version'],
            'sha256_hex'        => $sha256hex,
            'url'               => $url,
            'updated_at'        => now()->toDateTimeString(),
            'cert_checksum_b64' => $certChecksum,
            'wifi_ssid'         => $existing['wifi_ssid'] ?? '',
            'wifi_password'     => $existing['wifi_password'] ?? '',
            'wifi_security'     => $existing['wifi_security'] ?? 'WPA',
        ];

        file_put_contents(storage_path('app/apk/guardian-info.json'), json_encode($info, JSON_PRETTY_PRINT));

        config([
            'advertising.latest_apk_version' => $data['version'],
            'advertising.latest_apk_url'     => $url,
            'advertising.latest_apk_sha256'  => $sha256hex,
        ]);

        Notification::make()
            ->success()
            ->title('APK Guardiana actualizada')
            ->body("Versión {$data['version']} lista. SHA-256 archivo: {$sha256hex}")
            ->send();

        // Recargar el formulario de upload limpio y el de config con datos actuales
        $this->uploadForm->fill(['version' => $data['version'], 'cert_checksum_b64' => $certChecksum]);
        $this->configForm->fill([
            'cert_checksum_b64' => $certChecksum,
            'wifi_ssid'         => $info['wifi_ssid'],
            'wifi_password'     => $info['wifi_password'],
            'wifi_security'     => $info['wifi_security'],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}