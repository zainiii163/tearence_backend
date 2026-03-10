<?php

namespace App\Filament\Resources\ResortsTravelCategoryResource\Pages;

use App\Filament\Resources\ResortsTravelCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResortsTravelCategory extends ViewRecord
{
    protected static string $resource = ResortsTravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
