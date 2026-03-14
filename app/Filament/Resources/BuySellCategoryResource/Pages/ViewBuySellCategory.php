<?php

namespace App\Filament\Resources\BuySellCategoryResource\Pages;

use App\Filament\Resources\BuySellCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBuySellCategory extends ViewRecord
{
    protected static string $resource = BuySellCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
