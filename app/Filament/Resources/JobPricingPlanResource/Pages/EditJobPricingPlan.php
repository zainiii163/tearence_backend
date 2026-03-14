<?php

namespace App\Filament\Resources\JobPricingPlanResource\Pages;

use App\Filament\Resources\JobPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobPricingPlan extends EditRecord
{
    protected static string $resource = JobPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }
}
