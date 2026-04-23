<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvCampaignResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvCampaign extends CreateRecord
{
    protected static string $resource = AdvCampaignResource::class;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
