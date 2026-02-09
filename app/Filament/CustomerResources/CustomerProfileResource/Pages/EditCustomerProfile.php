<?php

namespace App\Filament\CustomerResources\CustomerProfileResource\Pages;

use App\Filament\CustomerResources\CustomerProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditCustomerProfile extends EditRecord
{
    protected static string $resource = CustomerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
