<?php

namespace App\Filament\Resources\JobAlertResource\Pages;

use App\Filament\Resources\JobAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobAlerts extends ListRecords
{
    protected static string $resource = JobAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

