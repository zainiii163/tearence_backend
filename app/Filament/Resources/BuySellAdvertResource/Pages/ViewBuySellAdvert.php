<?php

namespace App\Filament\Resources\BuySellAdvertResource\Pages;

use App\Filament\Resources\BuySellAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBuySellAdvert extends ViewRecord
{
    protected static string $resource = BuySellAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
