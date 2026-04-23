<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvTabletResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvTablet extends CreateRecord
{
    protected static string $resource = AdvTabletResource::class;

    protected function getRedirectUrl(): string
    {
        return AdvTabletResource::getUrl('index');
    }
}
