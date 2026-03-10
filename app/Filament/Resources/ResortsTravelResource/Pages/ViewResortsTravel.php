<?php

namespace App\Filament\Resources\ResortsTravelResource\Pages;

use App\Filament\Resources\ResortsTravelResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResortsTravel extends ViewRecord
{
    protected static string $resource = ResortsTravelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
