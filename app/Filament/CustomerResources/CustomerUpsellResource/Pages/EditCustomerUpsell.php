<?php

namespace App\Filament\CustomerResources\CustomerUpsellResource\Pages;

use App\Filament\CustomerResources\CustomerUpsellResource;
use Filament\Resources\Pages\EditRecord;

class EditCustomerUpsell extends EditRecord
{
    protected static string $resource = CustomerUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
