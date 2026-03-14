<?php

namespace App\Filament\Resources\VenueServiceResource\Pages;

use App\Filament\Resources\VenueServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVenueService extends ViewRecord
{
    protected static string $resource = VenueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
