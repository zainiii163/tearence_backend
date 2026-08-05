<?php

namespace App\Filament\Resources\PromoRewardCodeResource\Pages;

use App\Filament\Resources\PromoRewardCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromoRewardCodes extends ListRecords
{
    protected static string $resource = PromoRewardCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
