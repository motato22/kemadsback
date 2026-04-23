<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvTabletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvTablets extends ListRecords
{
    protected static string $resource = AdvTabletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
