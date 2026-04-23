<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvSurveys extends ListRecords
{
    protected static string $resource = AdvSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
