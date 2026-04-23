<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvAdvertiser extends CreateRecord
{
    protected static string $resource = AdvAdvertiserResource::class;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
