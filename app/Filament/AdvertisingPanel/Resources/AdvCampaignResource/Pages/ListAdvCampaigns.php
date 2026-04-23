<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvCampaignResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvCampaigns extends ListRecords
{
    protected static string $resource = AdvCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
