<?php

use App\Http\Controllers\Api\Advertising\AdvSurveyApiController;
use App\Http\Controllers\Api\Advertising\CampaignQrController;
use App\Http\Controllers\Api\Advertising\CampaignSyncController;
use App\Http\Controllers\Api\Advertising\HeartbeatController;
use App\Http\Controllers\Api\Advertising\PlaybackEventController;
use App\Http\Controllers\Api\Advertising\ProvisionController;
use App\Http\Controllers\Api\Advertising\SurveyController;
use App\Http\Controllers\Api\Advertising\SyncAckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema de Publicidad KEMADVERTISING
|--------------------------------------------------------------------------
*/

// ─── Descarga de APKs (sin autenticación) ─────
Route::get('/apk/guardian', function () {
    $path = storage_path('app/apk/guardian-latest.apk');
    if (! file_exists($path)) {
        abort(404, 'APK Guardiana no disponible.');
    }
    return response()->download($path, 'guardian-latest.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
    ]);
})->name('adv.apk.guardian');

Route::get('/apk/player', function () {
    $path = storage_path('app/public/apk/reproductora-latest.apk');
    if (! file_exists($path)) {
        abort(404, 'APK Reproductora no disponible.');
    }
    return response()->download($path, 'reproductora-latest.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
    ]);
})->name('adv.apk.player');

// ─── Aprovisionamiento (sin autenticación) ────────────────────────────────
Route::post('/provision', [ProvisionController::class, 'store'])
    ->name('adv.provision');

// ─── URL Pública para escaneo de QR (sin autenticación de tablet) ─────────
Route::get('/campaigns/{campaign}/qr', [CampaignQrController::class, 'redirect'])
    ->name('adv.campaigns.qr');

// ─── Endpoints autenticados por token de tablet ───────────────────────────
Route::middleware(['auth:sanctum', \App\Http\Middleware\Advertising\EnsureTabletAuthenticated::class])
    ->group(function () {
        // Heartbeat
        Route::post('/heartbeat', [HeartbeatController::class, 'store'])->name('adv.heartbeat');
        // Sincronización de campañas
        Route::get('/campaigns/sync', [CampaignSyncController::class, 'index'])->name('adv.campaigns.sync');
        // Registro de reproducciones
        Route::post('/playback-event', [PlaybackEventController::class, 'store'])->name('adv.playback-event');
        // Confirmación de sincronización de media
        Route::post('/campaigns/sync-ack', [SyncAckController::class, 'store'])->name('adv.campaigns.sync-ack');

        // Módulo 4: encuestas/trivia
        Route::get('/campaigns/{campaign}/survey', [AdvSurveyApiController::class, 'show'])->name('adv.campaigns.survey');
        Route::post('/surveys/{survey}/respond', [AdvSurveyApiController::class, 'storeResponse'])->name('adv.surveys.respond');
        Route::post('/survey-results', [SurveyController::class, 'store'])->name('adv.survey-results'); // Legacy guardado temporal
    });
