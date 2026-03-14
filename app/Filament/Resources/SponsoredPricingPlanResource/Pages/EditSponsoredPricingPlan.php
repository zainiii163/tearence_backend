<?php

namespace App\Filament\Resources\SponsoredPricingPlanResource\Pages;

use App\Filament\Resources\SponsoredPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsoredPricingPlan extends EditRecord
{
    protected static string $resource = SponsoredPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
