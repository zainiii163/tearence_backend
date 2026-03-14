<?php

namespace App\Filament\Resources\FundingPledgeResource\Pages;

use App\Filament\Resources\FundingPledgeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundingPledge extends EditRecord
{
    protected static string $resource = FundingPledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
