<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvAdvertisers extends ListRecords
{
    protected static string $resource = AdvAdvertiserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
