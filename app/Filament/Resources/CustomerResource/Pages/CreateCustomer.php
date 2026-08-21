<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password = $data['new_password'] ?? null;
        unset($data['new_password'], $data['password_hash']);

        if (filled($password)) {
            $data['password_hash'] = Hash::make((string) $password);
        }

        return $data;
    }
}
