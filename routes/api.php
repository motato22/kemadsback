<?php

use App\Filament\AdvertisingPanel\Pages\ManageGuardianApk;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});

// Temporal: ver valores que usa el QR de aprovisionamiento
Route::get('/debug-qr-config', function () {
    $info = ManageGuardianApk::getGuardianInfo();

    return [
        'component_name'      => config('advertising.guardian_component_name'),
        'apk_url'             => config('advertising.guardian_apk_url'),
        'sha256_hex'          => $info['sha256_hex'] ?? null,
        'cert_checksum_b64'   => $info['cert_checksum_b64'] ?? null,
    ];
});

// Temporal: ver provision_secret en caché para unit_id 1
Route::get('/debug-secret', function () {
    return ['secret' => cache()->get('adv:provision_secret:1')];
});

// Temporal: versiones de los APKs en el servidor (requiere aapt en build-tools)
Route::get('/debug-apk-versions', function () {
    $aapt = '/Users/zkbridge/Library/Android/sdk/build-tools/36.1.0/aapt';

    $guardianPath = Storage::disk('local')->path('apk/guardian-latest.apk');
    $playerPath   = Storage::disk('public')->path('apk/reproductora-latest.apk');

    $guardianInfo = file_exists($guardianPath)
        ? shell_exec("$aapt dump badging " . escapeshellarg($guardianPath) . " 2>/dev/null | grep versionName")
        : null;
    $playerInfo = file_exists($playerPath)
        ? shell_exec("$aapt dump badging " . escapeshellarg($playerPath) . " 2>/dev/null | grep versionName")
        : null;

    return [
        'guardian'     => $guardianInfo ? trim($guardianInfo) : 'APK no encontrado',
        'reproductora' => $playerInfo ? trim($playerInfo) : 'APK no encontrado',
    ];
});

// Temporal: prueba de la verdad — qué driver de caché está usando Laravel
Route::get('/debug-cache', function () {
    dd([
        '1_CACHE_STORE_ENV'   => env('CACHE_STORE'),
        '2_CACHE_DRIVER_ENV'  => env('CACHE_DRIVER'),
        '3_CONFIG_COMPILADA'  => config('cache.default'),
    ]);
});
