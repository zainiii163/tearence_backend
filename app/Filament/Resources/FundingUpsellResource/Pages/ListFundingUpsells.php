<?php

namespace App\Filament\Resources\FundingUpsellResource\Pages;

use App\Filament\Resources\FundingUpsellResource;
use Filament\Resources\Pages\ListRecords;

class ListFundingUpsells extends ListRecords
{
    protected static string $resource = FundingUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
