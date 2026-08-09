<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProperty extends CreateRecord
{
    protected static string $resource = PropertyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['description']) && !empty($data['overview'])) {
            $data['description'] = $data['overview'];
        }
        if (empty($data['description']) && !empty($data['title'])) {
            $data['description'] = $data['title'];
        }
        if (!isset($data['deposit_required']) && isset($data['deposit'])) {
            $data['deposit_required'] = $data['deposit'];
        }
        if (empty($data['currency'])) {
            $data['currency'] = 'USD';
        }

        return $data;
    }
}
