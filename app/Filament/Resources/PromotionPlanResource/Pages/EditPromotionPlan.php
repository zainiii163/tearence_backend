<?php

namespace App\Filament\Resources\PromotionPlanResource\Pages;

use App\Filament\Resources\PromotionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionPlan extends EditRecord
{
    protected static string $resource = PromotionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
