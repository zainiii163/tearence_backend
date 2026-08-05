<?php

namespace App\Filament\Resources\PromoPricingPlanResource\Pages;

use App\Filament\Resources\PromoPricingPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromoPricingPlan extends EditRecord
{
    protected static string $resource = PromoPricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
