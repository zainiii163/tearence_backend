<?php

namespace App\Filament\Resources\ResortsTravelResource\Pages;

use App\Filament\Resources\ResortsTravelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResortsTravels extends ListRecords
{
    protected static string $resource = ResortsTravelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
