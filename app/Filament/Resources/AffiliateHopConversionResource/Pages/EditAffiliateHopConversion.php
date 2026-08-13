<?php

namespace App\Filament\Resources\AffiliateHopConversionResource\Pages;

use App\Filament\Resources\AffiliateHopConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateHopConversion extends EditRecord
{
    protected static string $resource = AffiliateHopConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
