<?php

namespace App\Filament\Resources\PromotedAdvertCategoryResource\Pages;

use App\Filament\Resources\PromotedAdvertCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotedAdvertCategories extends ListRecords
{
    protected static string $resource = PromotedAdvertCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
