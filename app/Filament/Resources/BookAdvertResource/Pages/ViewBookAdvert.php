<?php

namespace App\Filament\Resources\BookAdvertResource\Pages;

use App\Filament\Resources\BookAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookAdvert extends ViewRecord
{
    protected static string $resource = BookAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
