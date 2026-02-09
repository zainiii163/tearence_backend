<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure customer_uid is set if not provided
        if (empty($data['customer_uid'])) {
            $data['customer_uid'] = Str::random(10);
        }

        // Hash password if provided
        if (!empty($data['password_hash'])) {
            $data['password_hash'] = bcrypt($data['password_hash']);
        }

        return $data;
    }
}
