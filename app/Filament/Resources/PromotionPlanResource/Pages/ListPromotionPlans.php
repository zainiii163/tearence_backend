<?php

namespace App\Filament\Resources\PromotionPlanResource\Pages;

use App\Filament\Resources\PromotionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionPlans extends ListRecords
{
    protected static string $resource = PromotionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
