<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema de Publicidad KEMADVERTISING
    |--------------------------------------------------------------------------
    */

    /*
     | URL pública del servidor (Ngrok, dominio real, o IP:puerto en LAN).
     | Las tablets y el comando ADB deben usar esta URL, no localhost.
     | Si no se define, se usa APP_URL (puede ser localhost en desarrollo).
     */
    'public_app_url' => env('ADV_PUBLIC_APP_URL', env('APP_URL', 'http://localhost:8000')),

    /*
     | Umbral en minutos para considerar una tablet como offline.
     | Las tablets envían heartbeat cada 3 minutos en condiciones normales.
     */
    'offline_threshold_minutes' => env('ADV_OFFLINE_THRESHOLD_MINUTES', 10),

    /*
     | Nivel de batería (%) que dispara una alerta.
     */
    'battery_alert_threshold' => env('ADV_BATTERY_ALERT_THRESHOLD', 20),

    /*
     | Email que recibe las alertas operativas (offline, batería baja, rollback).
     */
    'alert_email' => env('ADV_ALERT_EMAIL'),

    /*
     | APK Reproductora: versión más reciente para OTA.
     | Se actualiza automáticamente al subir el APK desde el panel.
     */
    'latest_apk_version' => env('ADV_LATEST_APK_VERSION'),
    'latest_apk_url'     => env('ADV_LATEST_APK_URL'),
    'latest_apk_sha256'  => env('ADV_LATEST_APK_SHA256'),

    /*
     | APK Guardiana: se sube manualmente desde el panel y nunca vía OTA.
     | El SHA-256 hex se usa en el JSON de aprovisionamiento ADB.
     | guardian_component_name: nombre del componente Activity para el intent ADB.
     */
    'guardian_apk_url'         => env('ADV_GUARDIAN_APK_URL'),
    'guardian_component_name'  => env('ADV_GUARDIAN_COMPONENT_NAME', 'com.kemadvertising.guardiana.debug/.MainActivity'),

    /*
     | Tiempo de expiración del QR de aprovisionamiento (horas).
     */
    'provision_qr_ttl_hours' => env('ADV_PROVISION_QR_TTL_HOURS', 24),

    /*
     | Disco de storage configurado para Cloudflare R2.
     | Ver filesystems.php > disks > r2
     */
    'storage_disk' => env('ADV_STORAGE_DISK', 'r2'),

    /*
     | Tiempo de vigencia de las URLs firmadas de R2 (minutos).
     | RefreshSignedUrlsJob debe ejecutarse antes de que expiren.
     */
    'signed_url_ttl_minutes' => env('ADV_SIGNED_URL_TTL_MINUTES', 2880), // 48 horas

    /*
     | Presupuesto de almacenamiento por tablet (en KB).
     | La app cliente puede usarlo para decidir cuánto contenido mantener
     | localmente antes de borrar media obsoleto.
     */
    'storage_budget_kb' => env('ADV_STORAGE_BUDGET_KB', 40 * 1024 * 1024), // 40 GB

    /*
     | Colas utilizadas por el sistema de publicidad.
     */
    'queues' => [
        'heartbeats' => env('ADV_QUEUE_HEARTBEATS', 'heartbeats'),
        'alerts'     => env('ADV_QUEUE_ALERTS', 'alerts'),
        'exports'    => env('ADV_QUEUE_EXPORTS', 'exports'),
        'default'    => env('ADV_QUEUE_DEFAULT', 'default'),
    ],

];
