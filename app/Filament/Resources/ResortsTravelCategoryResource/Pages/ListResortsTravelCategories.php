<?php

namespace App\Filament\Resources\ResortsTravelCategoryResource\Pages;

use App\Filament\Resources\ResortsTravelCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResortsTravelCategories extends ListRecords
{
    protected static string $resource = ResortsTravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
