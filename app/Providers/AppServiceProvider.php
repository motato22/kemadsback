<?php

namespace App\Providers;

use App\Models\Advertising\AdvAdvertiser;
use App\Models\Advertising\AdvCampaign;
use App\Models\Advertising\AdvCampaignGroup;
use App\Models\Advertising\AdvSurvey;
use App\Models\Advertising\AdvTablet;
use App\Policies\Advertising\AdvAdvertiserPolicy;
use App\Policies\Advertising\AdvCampaignGroupPolicy;
use App\Policies\Advertising\AdvCampaignPolicy;
use App\Policies\Advertising\AdvSurveyPolicy;
use App\Policies\Advertising\AdvTabletPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar políticas del panel de publicidad (Shield) para que el menú y acciones respeten permisos.
        Gate::policy(AdvCampaign::class, AdvCampaignPolicy::class);
        Gate::policy(AdvAdvertiser::class, AdvAdvertiserPolicy::class);
        Gate::policy(AdvCampaignGroup::class, AdvCampaignGroupPolicy::class);
        Gate::policy(AdvSurvey::class, AdvSurveyPolicy::class);
        Gate::policy(AdvTablet::class, AdvTabletPolicy::class);
        Gate::policy(Role::class, \App\Policies\RolePolicy::class);

        // En producción, forzar HTTPS para que todas las URLs generadas sean seguras.
        // Android Enterprise bloquea descargas por HTTP plano (Android 10+).
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
