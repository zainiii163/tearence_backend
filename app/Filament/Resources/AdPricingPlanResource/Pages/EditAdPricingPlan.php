<?php

namespace App\Filament\Resources\AdPricingPlanResource\Pages;

use App\Filament\Resources\AdPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdPricingPlan extends EditRecord
{
    protected static string $resource = AdPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
