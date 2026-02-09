<?php

namespace App\Filament\CustomerResources\CustomerUpsellResource\Pages;

use App\Filament\CustomerResources\CustomerUpsellResource;
use App\Models\ListingUpsell;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerUpsell extends CreateRecord
{
    protected static string $resource = CustomerUpsellResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['customer_id'] = auth()->user()->customer_id ?? 0;
        $data['payment_status'] = ListingUpsell::PAYMENT_PENDING;
        $data['status'] = ListingUpsell::STATUS_ACTIVE;
        
        return $data;
    }
}
