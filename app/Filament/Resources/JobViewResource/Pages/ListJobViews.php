<?php

namespace App\Filament\Resources\JobViewResource\Pages;

use App\Filament\Resources\JobViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobViews extends ListRecords
{
    protected static string $resource = JobViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
