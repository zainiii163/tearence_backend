<?php

namespace App\Filament\Resources\KycResource\Pages;

use App\Filament\Resources\KycResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKyc extends ViewRecord
{
    protected static string $resource = KycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
