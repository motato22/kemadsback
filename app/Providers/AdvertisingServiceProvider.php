<?php

namespace App\Providers;

use App\Jobs\Advertising\CheckOfflineTabletsJob;
use App\Jobs\Advertising\RefreshSignedUrlsJob;
use App\Models\Advertising\AdvMedia;
use App\Models\Advertising\AdvCampaignGroup;
use App\Observers\Advertising\AdvMediaObserver;
use App\Observers\Advertising\AdvCampaignGroupObserver;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider del sistema de publicidad.
 * Registra rutas, observadores, schedulers y bindings de forma completamente
 * aislada del resto de la aplicación.
 *
 * Registro en config/app.php > providers:
 *   App\Providers\AdvertisingServiceProvider::class,
 */
class AdvertisingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerObservers();
        $this->registerSchedule();
        $this->registerMigrations();
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api/adv')
            ->middleware('api')
            ->name('adv.')
            ->group(base_path('routes/api_advertising.php'));
    }

    protected function registerObservers(): void
    {
        AdvMedia::observe(AdvMediaObserver::class);
        AdvCampaignGroup::observe(AdvCampaignGroupObserver::class);
    }

    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // Verifica tablets offline cada 10 minutos
            $schedule->job(new CheckOfflineTabletsJob())
                ->everyTenMinutes()
                ->name('adv:check-offline-tablets')
                ->withoutOverlapping();

            // Renueva URLs firmadas de R2 cada 24 horas
            $schedule->job(new RefreshSignedUrlsJob())
                ->dailyAt('03:00')
                ->name('adv:refresh-signed-urls')
                ->withoutOverlapping();

            // Expira campañas cuya fecha ends_at ya pasó
            $schedule->command('adv:expire-campaigns')
                ->hourly()
                ->name('adv:expire-campaigns');
        });
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/advertising'));
    }
}
