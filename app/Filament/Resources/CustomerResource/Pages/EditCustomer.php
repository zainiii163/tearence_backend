<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Never put the hash into the password field
        unset($data['password_hash'], $data['new_password']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $password = $data['new_password'] ?? null;
        unset($data['new_password'], $data['password_hash']);

        if (filled($password)) {
            $data['password_hash'] = Hash::make((string) $password);
        }

        return $data;
    }
}
