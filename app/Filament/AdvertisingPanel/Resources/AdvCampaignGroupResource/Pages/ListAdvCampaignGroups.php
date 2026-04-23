<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvCampaignGroups extends ListRecords
{
    protected static string $resource = AdvCampaignGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
