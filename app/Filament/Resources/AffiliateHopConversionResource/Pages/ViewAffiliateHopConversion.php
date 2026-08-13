<?php

namespace App\Filament\Resources\AffiliateHopConversionResource\Pages;

use App\Filament\Resources\AffiliateHopConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAffiliateHopConversion extends ViewRecord
{
    protected static string $resource = AffiliateHopConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
