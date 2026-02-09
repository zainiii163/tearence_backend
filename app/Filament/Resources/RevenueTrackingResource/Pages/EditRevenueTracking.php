<?php

namespace App\Filament\Resources\RevenueTrackingResource\Pages;

use App\Filament\Resources\RevenueTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevenueTracking extends EditRecord
{
    protected static string $resource = RevenueTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

