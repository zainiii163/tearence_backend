<?php

namespace App\Filament\Resources\BookAdvertResource\Pages;

use App\Filament\Resources\BookAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookAdverts extends ListRecords
{
    protected static string $resource = BookAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
