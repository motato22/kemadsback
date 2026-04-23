<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvSurveyResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvSurveyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdvSurvey extends EditRecord
{
    protected static string $resource = AdvSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
