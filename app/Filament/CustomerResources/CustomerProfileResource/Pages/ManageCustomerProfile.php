<?php

namespace App\Filament\CustomerResources\CustomerProfileResource\Pages;

use App\Filament\CustomerResources\CustomerProfileResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerProfile extends ManageRecords
{
    protected static string $resource = CustomerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}
