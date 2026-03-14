<?php

namespace App\Filament\Resources\AffiliateUpsellPlanResource\Pages;

use App\Filament\Resources\AffiliateUpsellPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAffiliateUpsellPlan extends ViewRecord
{
    protected static string $resource = AffiliateUpsellPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
