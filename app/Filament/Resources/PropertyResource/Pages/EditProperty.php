<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['description']) && !empty($data['overview'])) {
            $data['description'] = $data['overview'];
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
