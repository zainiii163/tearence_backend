<?php

namespace App\Filament\Resources\CustomerBusinessResource\Pages;

use App\Filament\Resources\CustomerBusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerBusiness extends EditRecord
{
    protected static string $resource = CustomerBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['menu_samples_kv'] = CustomerBusinessResource::extractMenuSamplesKv(
            $data['category_profile'] ?? null
        );

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CustomerBusinessResource::normalizeCategoryProfileData($data);
    }
}
