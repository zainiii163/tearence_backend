<?php

namespace App\Filament\Resources\PropertyCategoryResource\Pages;

use App\Filament\Resources\PropertyCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertyCategory extends EditRecord
{
    protected static string $resource = PropertyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
