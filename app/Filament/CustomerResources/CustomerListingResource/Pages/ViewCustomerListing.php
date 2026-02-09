<?php

namespace App\Filament\CustomerResources\CustomerListingResource\Pages;

use App\Filament\CustomerResources\CustomerListingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerListing extends ViewRecord
{
    protected static string $resource = CustomerListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
