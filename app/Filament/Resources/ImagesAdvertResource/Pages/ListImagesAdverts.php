<?php

namespace App\Filament\Resources\ImagesAdvertResource\Pages;

use App\Filament\Resources\ImagesAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImagesAdverts extends ListRecords
{
    protected static string $resource = ImagesAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
