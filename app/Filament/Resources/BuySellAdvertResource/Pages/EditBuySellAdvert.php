<?php

namespace App\Filament\Resources\BuySellAdvertResource\Pages;

use App\Filament\Resources\BuySellAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuySellAdvert extends EditRecord
{
    protected static string $resource = BuySellAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
