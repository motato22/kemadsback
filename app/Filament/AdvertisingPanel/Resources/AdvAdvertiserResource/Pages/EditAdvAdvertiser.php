<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvAdvertiser extends EditRecord
{
    protected static string $resource = AdvAdvertiserResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
