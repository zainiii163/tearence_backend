<?php

namespace App\Filament\Resources\RevenueTrackingResource\Pages;

use App\Filament\Resources\RevenueTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRevenueTracking extends ViewRecord
{
    protected static string $resource = RevenueTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

