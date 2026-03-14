<?php

namespace App\Filament\Resources\BuySellPromotionPlanResource\Pages;

use App\Filament\Resources\BuySellPromotionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuySellPromotionPlan extends EditRecord
{
    protected static string $resource = BuySellPromotionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
