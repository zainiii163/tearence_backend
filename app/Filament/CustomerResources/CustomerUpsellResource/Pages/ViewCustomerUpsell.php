<?php

namespace App\Filament\CustomerResources\CustomerUpsellResource\Pages;

use App\Filament\CustomerResources\CustomerUpsellResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerUpsell extends ViewRecord
{
    protected static string $resource = CustomerUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
