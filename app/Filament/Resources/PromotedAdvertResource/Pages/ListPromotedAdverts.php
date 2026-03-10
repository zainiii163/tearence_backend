<?php

namespace App\Filament\Resources\PromotedAdvertResource\Pages;

use App\Filament\Resources\PromotedAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotedAdverts extends ListRecords
{
    protected static string $resource = PromotedAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
