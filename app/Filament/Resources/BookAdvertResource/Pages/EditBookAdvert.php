<?php

namespace App\Filament\Resources\BookAdvertResource\Pages;

use App\Filament\Resources\BookAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookAdvert extends EditRecord
{
    protected static string $resource = BookAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
