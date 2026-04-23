<?php

namespace App\Filament\AdvertisingPanel\Resources\UserResource\Pages;

use App\Filament\AdvertisingPanel\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->getRoleNames()->first();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        $role = $this->form->getState()['role'] ?? null;
        if ($role) {
            $this->record->syncRoles([$role]);
        }
    }
}
