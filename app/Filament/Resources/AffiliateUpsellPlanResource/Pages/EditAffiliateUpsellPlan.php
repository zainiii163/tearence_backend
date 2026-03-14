<?php

namespace App\Filament\Resources\AffiliateUpsellPlanResource\Pages;

use App\Filament\Resources\AffiliateUpsellPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateUpsellPlan extends EditRecord
{
    protected static string $resource = AffiliateUpsellPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
