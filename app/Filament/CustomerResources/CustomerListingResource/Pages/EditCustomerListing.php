<?php

namespace App\Filament\CustomerResources\CustomerListingResource\Pages;

use App\Filament\CustomerResources\CustomerListingResource;
use Filament\Resources\Pages\EditRecord;

class EditCustomerListing extends EditRecord
{
    protected static string $resource = CustomerListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
