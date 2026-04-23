<?php

namespace App\Filament\AdvertisingPanel\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ManagePlayerApk extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'APK Reproductora (OTA)';
    protected static ?string $navigationGroup = 'Dispositivos';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.advertising.pages.manage-player-apk';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->can('page_ManagePlayerApk'));
    }

    public function mount(): void
    {
        $infoPath = storage_path('app/public/apk/player-info.json');

        if (file_exists($infoPath)) {
            $info = json_decode(file_get_contents($infoPath), true);
            $this->form->fill(['version' => $info['version'] ?? '']);
        } else {
            $this->form->fill();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Subir actualización de la Reproductora')
                    ->description('Este APK se instalará de forma silenciosa en todas las tablets al recibir el próximo heartbeat.')
                    ->schema([
                        TextInput::make('version')
                            ->label('Versión del APK (ej. 1.0.5)')
                            ->required(),

                        FileUpload::make('apk_file')
                            ->label('Archivo APK')
                            ->acceptedFileTypes([
                                'application/vnd.android.package-archive',
                                'application/octet-stream',
                                'application/zip',
                                'application/x-zip-compressed',
                                'application/x-zip',
                            ])
                            ->disk('public')
                            ->directory('apk/temp')
                            ->maxSize(50 * 1024) // 50 MB (Filament usa KB)
                            ->helperText('Solo archivos .apk. Máximo 50 MB.')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar y Publicar APK')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data     = $this->form->getState();
        $tempPath = $data['apk_file'];
        $finalPath = 'apk/reproductora-latest.apk';

        if (Storage::disk('public')->exists($finalPath)) {
            Storage::disk('public')->delete($finalPath);
        }
        Storage::disk('public')->move($tempPath, $finalPath);

        $absolutePath = Storage::disk('public')->path($finalPath);
        $sha256       = hash_file('sha256', $absolutePath);

        $url = rtrim(config('app.url'), '/') . '/api/adv/apk/player';

        $info = [
            'version'    => $data['version'],
            'sha256'     => $sha256,
            'url'        => $url,
            'updated_at' => now()->toDateTimeString(),
        ];

        $dir = storage_path('app/public/apk');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(storage_path('app/public/apk/player-info.json'), json_encode($info, JSON_PRETTY_PRINT));

        Notification::make()
            ->success()
            ->title('APK publicado exitosamente')
            ->body("La versión {$data['version']} está lista. SHA-256 calculado automáticamente.")
            ->send();
    }
}
