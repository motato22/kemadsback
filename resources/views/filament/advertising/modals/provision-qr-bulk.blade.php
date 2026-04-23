@if (empty($items))
    <div class="py-6 text-center text-gray-500 dark:text-gray-400">
        No hay tablets en estado "Aprovisionando" en la selección.
    </div>
@else
    <div class="flex flex-col gap-8 py-4 max-h-[70vh] overflow-y-auto pr-1">
        @foreach ($items as $item)
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                {{-- QR --}}
                <div class="flex flex-col items-center gap-2 shrink-0">
                    <img
                        src="{{ $item['qrUrl'] }}"
                        alt="QR {{ $item['unitId'] }}"
                        class="w-48 h-48 rounded-lg border border-gray-200 dark:border-gray-700"
                    />
                    <a
                        href="{{ $item['qrDownloadUrl'] }}"
                        download="qr_{{ $item['unitId'] }}.png"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white px-3 py-1.5 text-xs font-semibold transition"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5" />
                        Guardar QR
                    </a>
                </div>

                {{-- Datos --}}
                <div class="w-full rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 text-xs font-mono">
                    <div class="flex items-start gap-3 px-3 py-2">
                        <span class="text-gray-500 dark:text-gray-400 shrink-0 w-28">server_url</span>
                        <span class="text-gray-900 dark:text-gray-100 break-all">{{ $item['serverUrl'] }}</span>
                    </div>
                    <div class="flex items-start gap-3 px-3 py-2">
                        <span class="text-gray-500 dark:text-gray-400 shrink-0 w-28">unit_id</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $item['unitId'] }}</span>
                    </div>
                    <div class="flex items-start gap-3 px-3 py-2">
                        <span class="text-gray-500 dark:text-gray-400 shrink-0 w-28">provision_secret</span>
                        <span class="text-gray-900 dark:text-gray-100 break-all">{{ $item['provisionSecret'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
