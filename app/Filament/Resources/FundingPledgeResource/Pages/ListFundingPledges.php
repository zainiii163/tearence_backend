<?php

namespace App\Filament\Resources\FundingPledgeResource\Pages;

use App\Filament\Resources\FundingPledgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundingPledges extends ListRecords
{
    protected static string $resource = FundingPledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
