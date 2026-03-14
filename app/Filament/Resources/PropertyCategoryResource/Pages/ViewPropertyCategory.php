<?php

namespace App\Filament\Resources\PropertyCategoryResource\Pages;

use App\Filament\Resources\PropertyCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPropertyCategory extends ViewRecord
{
    protected static string $resource = PropertyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
