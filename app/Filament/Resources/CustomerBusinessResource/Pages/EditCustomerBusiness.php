<?php

namespace App\Filament\Resources\CustomerBusinessResource\Pages;

use App\Filament\Resources\CustomerBusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerBusiness extends EditRecord
{
    protected static string $resource = CustomerBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
