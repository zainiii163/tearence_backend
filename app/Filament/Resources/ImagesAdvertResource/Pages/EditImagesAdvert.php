<?php

namespace App\Filament\Resources\ImagesAdvertResource\Pages;

use App\Filament\Resources\ImagesAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImagesAdvert extends EditRecord
{
    protected static string $resource = ImagesAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
