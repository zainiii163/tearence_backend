<?php

namespace App\Filament\Resources\BusinessAffiliateOfferResource\Pages;

use App\Filament\Resources\BusinessAffiliateOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessAffiliateOffer extends EditRecord
{
    protected static string $resource = BusinessAffiliateOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
