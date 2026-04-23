<?php

namespace App\Filament\AdvertisingPanel\Resources\AdvReportResource\Pages;

use App\Filament\AdvertisingPanel\Resources\AdvReportResource;
use Filament\Resources\Pages\ListRecords;

class ListAdvReports extends ListRecords
{
    protected static string $resource = AdvReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
