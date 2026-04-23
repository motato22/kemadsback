<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvSurvey extends CreateRecord
{
    protected static string $resource = AdvSurveyResource::class;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }
}
