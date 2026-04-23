<x-filament-panels::page>

    {{-- ── APK actual en servidor ──────────────────────────────────────── --}}
    @php
        $info   = \App\Filament\AdvertisingPanel\Pages\ManageGuardianApk::getGuardianInfo();
        $limits = $this->getPhpUploadLimits();
    @endphp

    @if ($info)
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">APK Guardiana actualmente en servidor</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Estado actual del APK y su configuración de aprovisionamiento.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Versión</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $info['version'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Última actualización</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $info['updated_at'] ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">SHA-256 del archivo APK</p>
                    <p class="font-mono text-xs break-all bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg select-all text-gray-800 dark:text-gray-200">{{ $info['sha256_hex'] ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Checksum del certificado (QR)</p>
                    @if (!empty($info['cert_checksum_b64']))
                        <p class="font-mono text-xs break-all bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg select-all text-gray-800 dark:text-gray-200">{{ $info['cert_checksum_b64'] }}</p>
                    @else
                        <p class="text-amber-600 dark:text-amber-400 text-xs font-medium">⚠ No configurado — el QR de aprovisionamiento no funcionará hasta que lo ingreses.</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">WiFi en QR</p>
                    @if (!empty($info['wifi_ssid']))
                        <p class="font-medium text-gray-900 dark:text-white">{{ $info['wifi_ssid'] }} <span class="text-gray-400">({{ $info['wifi_security'] ?? 'WPA' }})</span></p>
                    @else
                        <p class="text-gray-400 text-xs">No configurado</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">URL de descarga</p>
                    <a href="{{ $info['url'] ?? '#' }}" target="_blank"
                       class="text-primary-600 underline break-all text-xs">{{ $info['url'] ?? '—' }}</a>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl bg-amber-50 dark:bg-amber-950 ring-1 ring-amber-200 dark:ring-amber-800 p-5">
            <p class="text-amber-800 dark:text-amber-200 text-sm font-medium">
                ⚠ No hay ningún APK de Guardiana subido todavía. Completa el formulario de abajo para comenzar.
            </p>
        </div>
    @endif

    {{-- ── Formulario A: Checksum + WiFi (sin subir APK) ───────────────── --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Actualizar configuración del QR</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Puedes actualizar el checksum del certificado y las credenciales WiFi sin necesidad de subir una nueva versión del APK.</p>

        <x-filament-panels::form wire:submit="saveConfig">
            {{ $this->configForm }}
            <div class="mt-4">
                <x-filament::button type="submit" color="primary" icon="heroicon-o-check">
                    Guardar configuración
                </x-filament::button>
            </div>
        </x-filament-panels::form>
    </div>

    {{-- ── Formulario B: Subir APK nuevo ───────────────────────────────── --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">

        @if(! $limits['ok_for_apk'])
            <div class="mb-5 p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm">
                <p class="font-semibold">Límites de PHP insuficientes</p>
                <p class="mt-1 opacity-90">upload_max_filesize = <strong>{{ $limits['upload_max_filesize'] }}</strong> · post_max_size = <strong>{{ $limits['post_max_size'] }}</strong>. Necesitas al menos 15 MB.</p>
                <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-x-auto">upload_max_filesize = 64M
post_max_size = 64M</pre>
            </div>
        @endif

        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Subir nueva versión del APK</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Reemplaza el APK en el servidor. El WiFi y el checksum actuales se conservan a menos que ingreses uno nuevo.</p>

        <x-filament-panels::form wire:submit="uploadApk">
            {{ $this->uploadForm }}
            <div class="mt-4">
                <x-filament::button type="submit" color="success" icon="heroicon-o-arrow-up-tray">
                    Subir APK Guardiana
                </x-filament::button>
            </div>
        </x-filament-panels::form>
    </div>

</x-filament-panels::page>