<?php

namespace App\Filament\Resources\BusinessSocialPageResource\Pages;

use App\Filament\Resources\BusinessSocialPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessSocialPages extends ListRecords
{
    protected static string $resource = BusinessSocialPageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
