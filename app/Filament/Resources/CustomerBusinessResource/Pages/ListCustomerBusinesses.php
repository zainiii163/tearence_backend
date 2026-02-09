<?php

namespace App\Filament\Resources\CustomerBusinessResource\Pages;

use App\Filament\Resources\CustomerBusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerBusinesses extends ListRecords
{
    protected static string $resource = CustomerBusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
