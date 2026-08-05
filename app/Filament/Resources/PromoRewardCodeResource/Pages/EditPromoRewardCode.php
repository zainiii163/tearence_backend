<?php

namespace App\Filament\Resources\PromoRewardCodeResource\Pages;

use App\Filament\Resources\PromoRewardCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromoRewardCode extends EditRecord
{
    protected static string $resource = PromoRewardCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
