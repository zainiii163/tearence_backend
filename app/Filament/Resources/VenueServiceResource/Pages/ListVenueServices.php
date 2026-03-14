<?php

namespace App\Filament\Resources\VenueServiceResource\Pages;

use App\Filament\Resources\VenueServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVenueServices extends ListRecords
{
    protected static string $resource = VenueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
