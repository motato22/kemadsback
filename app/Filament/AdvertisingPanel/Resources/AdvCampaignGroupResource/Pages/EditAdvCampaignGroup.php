<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource;
use App\Models\Advertising\AdvCampaignGroup;
use App\Observers\Advertising\AdvCampaignGroupObserver;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditAdvCampaignGroup extends EditRecord
{
    protected static string $resource = AdvCampaignGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        /** @var AdvCampaignGroup $group */
        $group = $this->record;

        AdvCampaignGroupObserver::onTabletsSynced($group);

        Cache::tags(['adv:sync'])->flush();
    }
}
