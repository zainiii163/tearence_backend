<?php

namespace App\Filament\CustomerResources\CustomerListingResource\Pages;

use App\Filament\CustomerResources\CustomerListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerListing extends CreateRecord
{
    protected static string $resource = CustomerListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customer_id'] = auth()->user()->customer_id ?? 0;
        $data['approval_status'] = 'pending';
        
        return $data;
    }
}
