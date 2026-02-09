<?php

namespace App\Filament\Resources\CustomerBusinessResource\Pages;

use App\Filament\Resources\CustomerBusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerBusiness extends ViewRecord
{
    protected static string $resource = CustomerBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
