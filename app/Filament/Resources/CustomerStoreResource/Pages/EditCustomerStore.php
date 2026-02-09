<?php

namespace App\Filament\Resources\CustomerStoreResource\Pages;

use App\Filament\Resources\CustomerStoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerStore extends EditRecord
{
    protected static string $resource = CustomerStoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 