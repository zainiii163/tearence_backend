<?php

namespace App\Filament\Resources\SponsoredPricingPlanResource\Pages;

use App\Filament\Resources\SponsoredPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSponsoredPricingPlans extends ListRecords
{
    protected static string $resource = SponsoredPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
