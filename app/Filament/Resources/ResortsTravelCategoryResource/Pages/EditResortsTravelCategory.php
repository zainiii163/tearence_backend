<?php

namespace App\Filament\Resources\ResortsTravelCategoryResource\Pages;

use App\Filament\Resources\ResortsTravelCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResortsTravelCategory extends EditRecord
{
    protected static string $resource = ResortsTravelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
