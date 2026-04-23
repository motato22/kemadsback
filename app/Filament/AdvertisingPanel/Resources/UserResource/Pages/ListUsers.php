<?php

namespace App\Filament\AdvertisingPanel\Resources\UserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo usuario del panel'),
        ];
    }
}
