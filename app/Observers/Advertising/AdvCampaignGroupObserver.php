<?php

namespace App\Observers\Advertising;

use App\Models\Advertising\AdvCampaignGroup;
use App\Models\Advertising\AdvTablet;
use Illuminate\Support\Facades\Cache;

class AdvCampaignGroupObserver
{
    public function updated(AdvCampaignGroup $group): void
    {
        $this->markSyncRequired($group);
        Cache::tags(['adv:sync'])->flush();
    }

    public function deleted(AdvCampaignGroup $group): void
    {
        $this->markSyncRequired($group);
        Cache::tags(['adv:sync'])->flush();
    }

    /**
     * Marca como requerida la sincronización de campañas para todas las tablets
     * asociadas al grupo afectado.
     */
    private function markSyncRequired(AdvCampaignGroup $group): void
    {
        $group->loadMissing('tablets');

        $group->tablets->each(function (AdvTablet $tablet) {
            Cache::put(
                "adv:sync_required:{$tablet->id}",
                true,
                now()->addHours(48)
            );
        });
    }

    /**
     * Hook estático para ser llamado desde Filament cuando se sincronicen
     * tablets en el grupo (attach/detach en el pivot).
     */
    public static function onTabletsSynced(AdvCampaignGroup $group): void
    {
        $group->load('tablets');

        $group->tablets->each(function (AdvTablet $tablet) {
            Cache::put(
                "adv:sync_required:{$tablet->id}",
                true,
                now()->addHours(48)
            );
        });
    }
}

