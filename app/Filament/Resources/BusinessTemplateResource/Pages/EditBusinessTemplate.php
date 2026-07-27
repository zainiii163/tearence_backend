<?php

namespace App\Filament\Resources\BusinessTemplateResource\Pages;

use App\Filament\Resources\BusinessTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessTemplate extends EditRecord
{
    protected static string $resource = BusinessTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
