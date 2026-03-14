<?php

namespace App\Filament\Resources\BusinessAffiliateOfferResource\Pages;

use App\Filament\Resources\BusinessAffiliateOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessAffiliateOffer extends ViewRecord
{
    protected static string $resource = BusinessAffiliateOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
