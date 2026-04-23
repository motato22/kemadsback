# KEM Advertising — Backend

Backend Laravel 11 del sistema de publicidad en tablets para vehículos.  
Panel de administración Filament, API REST para tablets Android y sincronización de campañas.

> **Panel:** `https://tu-dominio.com/adv-panel`  
> **API base:** `https://tu-dominio.com/api/adv/`

---

## Requisitos del sistema

| Requisito | Versión mínima |
|-----------|---------------|
| PHP | 8.3 |
| MySQL | 8.0 |
| Redis | 7.x |
| Composer | 2.x |
| Laravel | 11.x |

**Extensiones PHP necesarias:** `pdo_mysql`, `redis` o `predis`, `zip`, `gd`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`.

---

## Configuración de desarrollo local

### 1. Clonar e instalar dependencias

```bash
git clone <repo>
cd "tabletas back"
composer install
```

### 2. Servicios con Docker

Levanta MySQL, Redis, MinIO y MailHog con un solo comando:

```bash
docker compose up -d
```

| Servicio | URL / Puerto | Credenciales |
|----------|-------------|--------------|
| MySQL | `localhost:3307` | `kem_user` / `kem_secret` (BD: `kem_advertising`) |
| Redis | `localhost:6379` | — |
| MinIO (S3 local) | API: `localhost:9000` — Consola: http://localhost:9001 | `minio` / `minio123` |
| MailHog | http://localhost:8025 | — |

> **Importante:** Redis es **obligatorio**. El código usa `Cache::tags(['adv:sync'])` que sólo funciona con el driver `redis`. Si no está activo verás: `"This cache store does not support tagging"`.

### 3. Variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Ajusta los valores críticos para desarrollo local:

```env
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=kem_advertising
DB_USERNAME=kem_user
DB_PASSWORD=kem_secret

REDIS_CLIENT=predis          # o phpredis si tienes la extensión instalada
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis             # ← OBLIGATORIO: no usar 'file' ni 'database'
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# MinIO (emula R2 en local)
CLOUDFLARE_R2_ACCESS_KEY_ID=minio
CLOUDFLARE_R2_SECRET_ACCESS_KEY=minio123
CLOUDFLARE_R2_BUCKET=kem-advertising-local
CLOUDFLARE_R2_ENDPOINT=http://127.0.0.1:9000
CLOUDFLARE_R2_URL=http://127.0.0.1:9000/kem-advertising-local

# URL pública que usan las tablets (usa Ngrok en local)
ADV_PUBLIC_APP_URL=https://xxx.ngrok-free.app
```

> Si usas `REDIS_CLIENT=predis`, instala el paquete: `composer require predis/predis`

### 4. Migraciones y storage

```bash
php artisan migrate
php artisan storage:link
```

### 5. Arrancar la app

```bash
# En terminales separadas:
php artisan serve          # App Laravel
php artisan horizon        # Workers de colas
php artisan schedule:work  # Scheduler (activar/expirar campañas cada minuto)
```

### 6. Crear superadmin del panel

```bash
php artisan adv:super-admin admin@empresa.com --password=contraseña_segura
```

**Panel disponible en:** `http://localhost:8000/adv-panel`

---

## Resumen rápido: comandos al clonar

```bash
git clone <repo> && cd "tabletas back"
composer install
docker compose up -d
cp .env.example .env && php artisan key:generate
# — editar .env con los valores de arriba —
php artisan config:clear
php artisan migrate
php artisan storage:link
php artisan adv:super-admin admin@empresa.com --password=secreto
php artisan serve
```

---

## 🚀 Checklist de Despliegue a Producción

Al desplegar en el servidor de producción sigue estos pasos **en orden**. Saltarse cualquiera puede causar caídas en la app móvil o errores de caché.

### 1. Actualizar el código y base de datos

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

### 2. Configurar el .env de producción

Variables críticas que deben estar presentes y correctas:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Redis — OBLIGATORIO para Cache::tags
CACHE_STORE=redis
REDIS_CLIENT=predis           # o phpredis
REDIS_HOST=127.0.0.1          # ajusta si Redis está en otro servidor
REDIS_PASSWORD=contraseña_redis_segura
REDIS_PORT=6379

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Cloudflare R2 — storage real de media
CLOUDFLARE_R2_ACCESS_KEY_ID=<tu_access_key>
CLOUDFLARE_R2_SECRET_ACCESS_KEY=<tu_secret_key>
CLOUDFLARE_R2_DEFAULT_REGION=auto
CLOUDFLARE_R2_BUCKET=kem-advertising
CLOUDFLARE_R2_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://cdn.tu-dominio.com  # o la URL pública de R2

# URL que usan las tablets para conectarse al servidor
ADV_PUBLIC_APP_URL=https://tu-dominio.com

# Email transaccional (Resend recomendado)
MAIL_MAILER=resend
RESEND_API_KEY=<tu_api_key>
MAIL_FROM_ADDRESS=noreply@tu-dominio.com

# Alertas del sistema
ADV_ALERT_EMAIL=admin@tu-empresa.com
```

### 3. Limpiar y recompilar la memoria (CRÍTICO)

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Configurar el Cron Job

En el servidor (o en Forge UI), añade **una sola entrada** que ejecuta todos los schedulers:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Esto activa/expira campañas por fecha automáticamente y lanza los jobs periódicos (renovación de URLs R2, alertas de tablets offline).

### 5. Configurar Supervisor para Horizon

Crea `/etc/supervisor/conf.d/kem-horizon.conf` (Forge lo hace automáticamente si lo configuras desde el panel):

```ini
[program:kem-horizon]
process_name=%(program_name)s
command=php /ruta/al/proyecto/artisan horizon
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/ruta/al/proyecto/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start kem-horizon
```

### 6. Reiniciar los procesos en segundo plano

Los workers y Horizon guardan config y código en RAM; deben reiniciarse para adoptar los cambios:

```bash
php artisan horizon:terminate   # Supervisor lo vuelve a levantar automáticamente
# o si usas colas nativas:
php artisan queue:restart
```

### 7. Configurar Nginx (ejemplo básico)

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /ruta/al/proyecto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

> En producción **nunca** usar `php artisan serve`. Nginx + PHP-FPM es obligatorio.

---

## Variables de entorno — referencia completa

Todas las variables con descripción están en `.env.example`. Las más importantes para el módulo de publicidad:

| Variable | Descripción |
|----------|-------------|
| `CACHE_STORE` | **Debe ser `redis`** — requerido por `Cache::tags()` |
| `ADV_PUBLIC_APP_URL` | URL que usan las tablets para conectarse. En local usa Ngrok |
| `CLOUDFLARE_R2_ENDPOINT` | Endpoint de R2. En local apunta a MinIO |
| `ADV_OFFLINE_THRESHOLD_MINUTES` | Minutos sin heartbeat antes de marcar tablet offline (default: 10) |
| `ADV_BATTERY_ALERT_THRESHOLD` | % de batería para disparar alerta (default: 20) |
| `ADV_ALERT_EMAIL` | Email que recibe las alertas del sistema |
| `ADV_PROVISION_QR_TTL_HOURS` | Horas de vigencia del secret de aprovisionamiento (default: 24) |
| `ADV_SIGNED_URL_TTL_MINUTES` | TTL de URLs firmadas de R2 (default: 2880 = 48h) |
| `HORIZON_ENABLED` | Activa/desactiva Horizon. Poner `false` en pruebas sin Redis |

---

## Comandos Artisan del módulo

```bash
# Expira campañas cuya fecha ends_at ya pasó (ejecutado por Scheduler cada hora)
php artisan adv:expire-campaigns

# Crear superadmin del panel
php artisan adv:super-admin correo@empresa.com --password=contraseña

# Ejecutar sólo las migraciones del módulo ADV
php artisan migrate --path=database/migrations/advertising

# Iniciar Horizon (workers de colas)
php artisan horizon
```

---

## Infraestructura recomendada para producción

| Componente | Servicio | Costo aprox. |
|-----------|----------|-------------|
| VPS | DigitalOcean 4 GB RAM | ~$24/mes |
| Deploy / Nginx / SSL / Supervisor | Laravel Forge | ~$39/mes |
| Media storage | Cloudflare R2 | ~$1.50/mes (100 tablets) |
| Email transaccional | Resend | Gratis hasta 3K emails/mes |

---

## Resolución de problemas comunes

| Error | Causa probable | Solución |
|-------|---------------|----------|
| `This cache store does not support tagging` | `CACHE_STORE` no es `redis` o Redis no corre | Verificar `.env` y ejecutar `php artisan config:clear` |
| `Connection refused` a Redis | Redis no está corriendo | `docker compose up -d` en local; verificar Supervisor en producción |
| `403` al descargar media desde la tablet | URL firmada de R2 expirada | `php artisan adv:refresh-signed-urls` o esperar el job diario de las 03:00 |
| Panel `/adv-panel` redirige al login sin avanzar | Sesión/cookie mal configurada | Verificar `SANCTUM_STATEFUL_DOMAINS` y `SESSION_DOMAIN` en `.env` |
| Campañas no se activan automáticamente | Cron job no configurado | Añadir `* * * * * php artisan schedule:run` en crontab del servidor |
# kemadsback
