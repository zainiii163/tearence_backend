<?php

namespace App\Filament\Resources\JobViewResource\Pages;

use App\Filament\Resources\JobViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobView extends EditRecord
{
    protected static string $resource = JobViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
