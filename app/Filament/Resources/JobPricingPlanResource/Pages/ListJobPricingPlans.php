<?php

namespace App\Filament\Resources\JobPricingPlanResource\Pages;

use App\Filament\Resources\JobPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobPricingPlans extends ListRecords
{
    protected static string $resource = JobPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
