# KEM Advertising — Arquitectura Técnica Backend
**KEMADVERTISING S.A. de C.V. | EMPBRIDGE S.A. de C.V. | Febrero 2026**

---

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Framework | Laravel | 11.x |
| Panel administrativo | Filament PHP | 3.x |
| Autenticación API | Laravel Sanctum | 4.x |
| Colas y workers | Laravel Horizon + Redis | 5.x / 7.x |
| Base de datos | MySQL | 8.0 |
| Almacenamiento media | Cloudflare R2 (S3-compatible) | — |
| Email transaccional | Resend | — |
| Servidor | DigitalOcean VPS + Laravel Forge | — |
| PHP | PHP | 8.3 |

---

## Principio de aislamiento total

Todo el sistema de publicidad vive bajo el namespace `Advertising` y usa el prefijo
`adv_` en todas las tablas. **No se toca ningún archivo del sistema existente.**

| Área | Aislamiento |
|---|---|
| Rutas API | `routes/api_advertising.php` registrado via `AdvertisingServiceProvider` |
| Panel Filament | `AdvertisingPanelProvider` en `/adv-panel` |
| Modelos | `app/Models/Advertising/` |
| Controladores | `app/Http/Controllers/Api/Advertising/` |
| Jobs | `app/Jobs/Advertising/` |
| Migraciones | `database/migrations/advertising/` |
| Tablas | Prefijo `adv_` en todas |

---

## Modelo de dominio (entidades principales)

```
AdvAdvertiser (1) ──────────────────── (N) AdvCampaign
                                              │
                                    (N) AdvMedia (videos/imágenes)
                                    │         │
                              AdvTabletMedia  │
                              (estado local   │
                               descarga)      │
                                    (1) AdvSurvey ── (N) AdvQuestion ── (N) AdvOption
                                              │            │
                                              │     (N) AdvSurveyResponse (email capturado)
                                              │
AdvCampaignGroup ─── pivot ─── AdvCampaign   │
AdvCampaignGroup ─── pivot ─── AdvTablet     │
                                              │
AdvTablet (1) ─── (N) AdvDriverShift         │
          │                   │               │
          │                   └──── (N) AdvPlaybackLog
          │                                   │
          ├── (N) AdvHeartbeatLog             └── (N) AdvSurveyResult
          ├── (N) AdvProvisioningLog
          ├── (N) AdvQrScan (escaneos de QR de campaña)
          └── (N) AdvPlaybackLog
```

---

## Flujo de aprovisionamiento de tablet

1. Admin crea tablet en Filament (`status = provisioning`)
2. Admin hace click en "Generar QR" → secret con TTL 24h en Redis
3. Técnico escanea QR con la App Guardiana
4. `POST /api/adv/provision` → valida secret, emite token Sanctum, `status = active`
5. Token se almacena en Android Keystore (nunca en texto plano)

---

## Flujo del ciclo de vida de una campaña

```
Filament: Admin sube media
    ↓
AdvMediaObserver: calcula MD5, genera URL firmada R2
    ↓
RefreshSignedUrlsJob (diario): renueva URLs antes de expirar
    ↓
Cache invalida: adv:sync_required:{tablet_id} = true
    ↓
Tablet heartbeat → servidor responde sync_required = true
    ↓
WorkManager: SyncWorker → GET /api/adv/campaigns/sync
    ↓
Room local actualizada → reproductor incluye nuevo contenido
    ↓
Cada reproducción: POST /api/adv/playback-event (batch offline-safe)
    ↓
ExpireCampaignsCommand (hourly): status = expired cuando ends_at < now
```

---

## API Endpoints

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| GET | `/api/adv/apk/guardian` | Pública | Descarga APK Guardiana (latest) |
| GET | `/api/adv/apk/player` | Pública | Descarga APK Reproductora (latest) |
| POST | `/api/adv/provision` | Pública | Aprovisionamiento inicial de tablet |
| GET | `/api/adv/campaigns/{id}/qr` | Pública | Redirect URL de QR de campaña |
| POST | `/api/adv/heartbeat` | Sanctum | Estado de tablet + comandos pendientes |
| GET | `/api/adv/campaigns/sync` | Sanctum | Catálogo de campañas activas |
| POST | `/api/adv/campaigns/sync-ack` | Sanctum | Confirmación de descarga de media |
| POST | `/api/adv/playback-event` | Sanctum | Log de reproducciones (batch) |
| GET | `/api/adv/campaigns/{id}/survey` | Sanctum | Obtener encuesta/trivia de campaña |
| POST | `/api/adv/surveys/{id}/respond` | Sanctum | Guardar respuesta de encuesta |
| POST | `/api/adv/survey-results` | Sanctum | Resultados de encuestas legacy (batch) |

> **Nota:** Los endpoints de turno de chofer (`driver-shift`) tienen controlador implementado
> (`DriverShiftController`) pero las rutas **aún no están registradas** — ver brecha #1.

---

## Colas (Laravel Horizon)

| Cola | Workers min/max | Propósito | Jobs asociados |
|---|---|---|---|
| `heartbeats` | 2 / 8 | Logs de heartbeat (alta frecuencia) | `ProcessHeartbeatJob` |
| `alerts` | 1 / 3 | Emails de alerta (anti-flood 30 min) | `SendHeartbeatAlertJob`, `CheckOfflineTabletsJob` |
| `exports` | 1 / 2 | Reportes CSV asíncronos | `ExportReportJob` |
| `default` | 1 / 4 | Renovación URLs R2, expiración campañas | `RefreshSignedUrlsJob` |

---

## Política de URLs firmadas R2 (cliente Android)

- Las URLs de `cdn_url` tienen un TTL de `signed_url_ttl_minutes` (default: 2880 min / 48h).
- El backend renueva todas las URLs diariamente a las 03:00 y marca `sync_required = true`
  en todas las tablets activas.
- **La app DEBE:**
  1. Siempre descargar media inmediatamente después de recibir el payload de `/campaigns/sync`,
     no cachear el payload para descargar después.
  2. Si recibe un error `403` o "URL expirada" al descargar, llamar a `/campaigns/sync`
     de nuevo para obtener URLs frescas antes de reintentar.
  3. Si el payload local de sync tiene más de 12 horas, ignorarlo y forzar un nuevo sync
     aunque `sync_required` esté en `false` en el heartbeat.
  4. Nunca persistir `cdn_url` en Room como fuente permanente — es efímera.
     Solo guardar `media_id`, `md5_hash` y `local_path`.

---

## Comandos Artisan

```bash
# Expira campañas vencidas (ejecutado por Scheduler cada hora)
php artisan adv:expire-campaigns

# Inicia Horizon (monitoreo de colas)
php artisan horizon

# Ejecutar todas las migraciones de publicidad
php artisan migrate --path=database/migrations/advertising
```

---

## Instalación en desarrollo

```bash
# 1. Clonar e instalar dependencias
composer install

# 2. Levantar servicios (MySQL, Redis, MinIO, MailHog)
docker compose up -d

# 3. Configurar variables de entorno
cp .env.local .env
php artisan key:generate

# 4. Correr migraciones del módulo
php artisan migrate --path=database/migrations/advertising

# 5. Crear panel Filament y registrar el provider
php artisan filament:install --panels
# Agregar AdvertisingServiceProvider y AdvertisingPanelProvider en bootstrap/providers.php

# 6. Levantar servidor y workers
php artisan serve
php artisan horizon
php artisan schedule:work
```

---

## Integración con infraestructura de producción (Forge + DigitalOcean)

1. **VPS**: DigitalOcean 4GB RAM Basic Shared ($24/mes) — Fase 1-3
2. **Forge**: Business Plan ($39/mes) — gestiona Nginx, SSL, Supervisor, deploys
3. **R2**: Cloudflare R2 (~$1.50/mes a 100 tablets) — media storage
4. **Horizon**: Supervisor configurado por Forge para mantener workers activos
5. **Scheduler**: Cron job `* * * * * php /path/artisan schedule:run` configurado en Forge UI

---

## Brechas críticas pendientes de decisión

| # | Brecha | Impacto |
|---|---|---|
| 1 | Rutas de turno de chofer sin registrar (`driver-shift`) | Endpoints del controlador inaccesibles; reportes por chofer bloqueados |
| 2 | Cambio de chofer por turno (PIN o Filament) | Reportes por chofer inválidos sin esto |
| 3 | Definición de "reproducción" (inicio, 50%, 100%) | Dashboard de reproducciones bloqueado |
| 4 | PIN de acceso técnico (global vs individual) | Módulo de acceso puede rediseñarse |
| 5 | Consentimiento GPS (LFPDPPP) | GPS en heartbeats desactivado hasta aclarar |
| 6 | Proceso de actualización de App Guardiana | Requiere ventanas de mantenimiento físico |
| 7 | Acceso del anunciante al panel | Define si se necesita rol 'advertiser' en Filament |

---

*Documento generado por EMPBRIDGE S.A. de C.V. — Última revisión: Abril 2026*
