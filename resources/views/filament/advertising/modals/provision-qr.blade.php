<div class="flex flex-col items-center gap-5 py-2">

    {{-- Estado del QR --}}
    @if($missingApk)
        <div class="w-full rounded-xl bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-800 dark:text-red-200">
            <p class="font-semibold">⚠ APK de Guardiana no subido</p>
            <p class="mt-0.5 text-xs opacity-90">El QR no funcionará hasta que subas el APK desde <strong>APK Guardiana</strong> en el panel.</p>
        </div>
    @else
        <div class="w-full rounded-xl border px-4 py-3 text-sm
            {{ !empty($certChecksum) ? 'bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-amber-50 dark:bg-amber-950 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200' }}">
            @if(!empty($certChecksum))
                <p class="font-semibold">✓ QR listo para aprovisionar</p>
                <p class="mt-0.5 text-xs opacity-90">
                    APK v{{ $apkVersion }} · Certificado verificado
                    @if($wifiSsid)
                        · WiFi <strong>{{ $wifiSsid }}</strong> incluido
                    @else
                        · Sin WiFi (la tablet deberá conectarse manualmente)
                    @endif
                </p>
            @else
                <p class="font-semibold">⚠ Checksum del certificado no configurado</p>
                <p class="mt-0.5 text-xs opacity-90">El QR puede funcionar en algunos dispositivos, pero Android podría rechazar el APK por falta de verificación de firma. Configúralo en <strong>APK Guardiana</strong>.
                    @if($wifiSsid)
                        · WiFi <strong>{{ $wifiSsid }}</strong> incluido.
                    @else
                        · Sin WiFi guardado (la tablet deberá conectarse manualmente).
                    @endif
                </p>
            @endif
        </div>
    @endif

    {{-- QR --}}
    <img
        src="{{ $qrUrl }}"
        alt="QR {{ $unitId }}"
        class="w-56 h-56 rounded-xl border border-gray-200 dark:border-gray-700 shrink-0"
    />

    {{-- Datos de aprovisionamiento --}}
    <div class="w-full overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 font-mono text-sm">

        <div class="px-4 py-3">
            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">server_url</p>
            <p class="text-gray-900 dark:text-gray-100 break-all">{{ $serverUrl }}</p>
        </div>

        <div class="px-4 py-3">
            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">unit_id</p>
            <p class="text-gray-900 dark:text-gray-100 break-all">{{ $unitId }}</p>
        </div>

        <div class="px-4 py-3">
            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">provision_secret</p>
            <p class="text-gray-900 dark:text-gray-100 break-all text-xs">{{ $provisionSecret }}</p>
        </div>

    </div>

    {{-- Botón guardar QR --}}
    <a
        href="{{ $qrDownloadUrl }}"
        download="qr_{{ $unitId }}.png"
        class="w-full flex items-center justify-center gap-2 rounded-xl bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white px-4 py-2.5 text-sm font-semibold transition"
    >
        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4 shrink-0" />
        Guardar QR como imagen
    </a>

</div>