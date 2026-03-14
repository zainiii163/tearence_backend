<?php

namespace App\Filament\Resources\BuySellCategoryResource\Pages;

use App\Filament\Resources\BuySellCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuySellCategories extends ListRecords
{
    protected static string $resource = BuySellCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
