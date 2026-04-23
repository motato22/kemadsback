<?php

namespace App\Filament\AdvertisingPanel\Resources\UserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $roleToAssign = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $role = $this->roleToAssign ?? null;
        if ($role) {
            $this->record->assignRole($role);
        }
    }
}
