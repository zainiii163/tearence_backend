<?php

namespace App\Filament\Resources\FundingRewardResource\Pages;

use App\Filament\Resources\FundingRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundingReward extends EditRecord
{
    protected static string $resource = FundingRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
