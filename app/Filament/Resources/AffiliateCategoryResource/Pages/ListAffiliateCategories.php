<?php

namespace App\Filament\Resources\AffiliateCategoryResource\Pages;

use App\Filament\Resources\AffiliateCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateCategories extends ListRecords
{
    protected static string $resource = AffiliateCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
