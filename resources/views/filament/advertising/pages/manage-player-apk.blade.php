<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    @php
        $infoPath = storage_path('app/public/apk/player-info.json');
        $info = file_exists($infoPath) ? json_decode(file_get_contents($infoPath), true) : null;
    @endphp

    @if($info)
        <div class="mt-8 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <h3 class="text-lg font-bold mb-4">APK actualmente en producción</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Versión Activa</p>
                    <p class="font-medium">{{ $info['version'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Última actualización</p>
                    <p class="font-medium">{{ $info['updated_at'] }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">SHA-256 (Validación de integridad)</p>
                    <p class="font-mono text-xs break-all bg-gray-100 dark:bg-gray-800 p-2 rounded">{{ $info['sha256'] }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">URL de descarga pública</p>
                    <a href="{{ $info['url'] }}" target="_blank" class="text-primary-600 underline break-all text-sm">{{ $info['url'] }}</a>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
