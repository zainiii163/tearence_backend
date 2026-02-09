<?php

namespace App\Filament\CustomerResources\CustomerUpsellResource\Pages;

use App\Filament\CustomerResources\CustomerUpsellResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerUpsells extends ListRecords
{
    protected static string $resource = CustomerUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
