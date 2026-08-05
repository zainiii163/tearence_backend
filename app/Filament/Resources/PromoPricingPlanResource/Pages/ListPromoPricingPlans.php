<?php

namespace App\Filament\Resources\PromoPricingPlanResource\Pages;

use App\Filament\Resources\PromoPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromoPricingPlans extends ListRecords
{
    protected static string $resource = PromoPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
