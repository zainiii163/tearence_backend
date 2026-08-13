<?php

namespace App\Filament\Resources\AffiliateHopConversionResource\Pages;

use App\Filament\Resources\AffiliateHopConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateHopConversions extends ListRecords
{
    protected static string $resource = AffiliateHopConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
