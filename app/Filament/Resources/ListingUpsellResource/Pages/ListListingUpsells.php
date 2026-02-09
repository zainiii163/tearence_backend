<?php

namespace App\Filament\Resources\ListingUpsellResource\Pages;

use App\Filament\Resources\ListingUpsellResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListingUpsells extends ListRecords
{
    protected static string $resource = ListingUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
