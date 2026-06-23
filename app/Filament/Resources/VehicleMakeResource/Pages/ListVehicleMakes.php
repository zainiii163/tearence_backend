<?php

namespace App\Filament\Resources\VehicleMakeResource\Pages;

use App\Filament\Resources\VehicleMakeResource;
use Filament\Resources\Pages\ListRecords;

class ListVehicleMakes extends ListRecords
{
    protected static string $resource = VehicleMakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
