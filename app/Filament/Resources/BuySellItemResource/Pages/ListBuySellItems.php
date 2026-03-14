<?php

namespace App\Filament\Resources\BuySellItemResource\Pages;

use App\Filament\Resources\BuySellItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuySellItems extends ListRecords
{
    protected static string $resource = BuySellItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
