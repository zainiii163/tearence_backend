<?php

namespace App\Filament\Resources\JobPricingPlanResource\Pages;

use App\Filament\Resources\JobPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewJobPricingPlan extends ViewRecord
{
    protected static string $resource = JobPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
