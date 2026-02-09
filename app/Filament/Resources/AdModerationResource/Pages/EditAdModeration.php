<?php

namespace App\Filament\Resources\AdModerationResource\Pages;

use App\Filament\Resources\AdModerationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdModeration extends EditRecord
{
    protected static string $resource = AdModerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
