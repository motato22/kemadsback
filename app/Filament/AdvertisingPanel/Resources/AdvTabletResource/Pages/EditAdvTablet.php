<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvTabletResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvTabletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvTablet extends EditRecord
{
    protected static string $resource = AdvTabletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
