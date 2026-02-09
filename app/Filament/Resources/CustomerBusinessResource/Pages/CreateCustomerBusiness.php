<?php

namespace App\Filament\Resources\CustomerBusinessResource\Pages;

use App\Filament\Resources\CustomerBusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCustomerBusiness extends CreateRecord
{
    protected static string $resource = CustomerBusinessResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate slug from business name if not provided
        if (empty($data['slug']) && !empty($data['business_name'])) {
            $data['slug'] = Str::slug($data['business_name']);
        }

        return $data;
    }
}
