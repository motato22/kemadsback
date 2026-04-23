<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource;
use App\Models\Advertising\AdvCampaignGroup;
use App\Observers\Advertising\AdvCampaignGroupObserver;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateAdvCampaignGroup extends CreateRecord
{
    protected static string $resource = AdvCampaignGroupResource::class;

    protected function afterCreate(): void
    {
        /** @var AdvCampaignGroup $group */
        $group = $this->record;

        if ($group->tablets()->exists()) {
            AdvCampaignGroupObserver::onTabletsSynced($group);
        }

        Cache::tags(['adv:sync'])->flush();
    }
}
