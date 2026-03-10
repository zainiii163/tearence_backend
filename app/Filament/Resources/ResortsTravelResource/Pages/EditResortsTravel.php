<?php

namespace App\Filament\Resources\ResortsTravelResource\Pages;

use App\Filament\Resources\ResortsTravelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResortsTravel extends EditRecord
{
    protected static string $resource = ResortsTravelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
