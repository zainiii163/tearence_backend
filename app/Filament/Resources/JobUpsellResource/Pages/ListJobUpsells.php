<?php

namespace App\Filament\Resources\JobUpsellResource\Pages;

use App\Filament\Resources\JobUpsellResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobUpsells extends ListRecords
{
    protected static string $resource = JobUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

