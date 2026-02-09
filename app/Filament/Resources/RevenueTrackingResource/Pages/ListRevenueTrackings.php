<?php

namespace App\Filament\Resources\RevenueTrackingResource\Pages;

use App\Filament\Resources\RevenueTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevenueTrackings extends ListRecords
{
    protected static string $resource = RevenueTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

