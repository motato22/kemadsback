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

        // Borrar el payload cacheado de cada tablet del grupo directamente.
        // Cache::tags(['adv:sync'])->flush() no funciona porque los payloads se guardan
        // sin tags en CampaignSyncController::index() → esas claves son invisibles para tags.
        $group->loadMissing('tablets');
        $group->tablets->each(function ($tablet) {
            Cache::forget("adv:sync_payload:{$tablet->id}");
        });
    }
}
