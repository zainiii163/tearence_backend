<?php

namespace App\Filament\Resources\VehicleEnquiryResource\Pages;

use App\Filament\Resources\VehicleEnquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListVehicleEnquiries extends ListRecords
{
    protected static string $resource = VehicleEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for enquiries
        ];
    }
}
