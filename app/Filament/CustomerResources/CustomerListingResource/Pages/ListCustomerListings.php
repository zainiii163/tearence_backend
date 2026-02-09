<?php

namespace App\Filament\CustomerResources\CustomerListingResource\Pages;

use App\Filament\CustomerResources\CustomerListingResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerListings extends ListRecords
{
    protected static string $resource = CustomerListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
