<?php

namespace App\Observers\Advertising;

use App\Models\Advertising\AdvCampaignGroup;
use App\Models\Advertising\AdvTablet;
use Illuminate\Support\Facades\Cache;

class AdvCampaignGroupObserver
{
    public function updated(AdvCampaignGroup $group): void
    {
        $this->invalidateTabletCaches($group);
    }

    public function deleted(AdvCampaignGroup $group): void
    {
        $this->invalidateTabletCaches($group);
    }

    /**
     * Marca sync_required y borra el payload cacheado para cada tablet del grupo.
     * Nota: Cache::tags(['adv:sync'])->flush() NO funciona aquí porque los payloads
     * se almacenan sin tags en CampaignSyncController::index().
     */
    private function invalidateTabletCaches(AdvCampaignGroup $group): void
    {
        $group->loadMissing('tablets');

        $group->tablets->each(function (AdvTablet $tablet) {
            Cache::put("adv:sync_required:{$tablet->id}", true, now()->addHours(48));
            Cache::forget("adv:sync_payload:{$tablet->id}");
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
            Cache::put("adv:sync_required:{$tablet->id}", true, now()->addHours(48));
            Cache::forget("adv:sync_payload:{$tablet->id}");
        });
    }
}

