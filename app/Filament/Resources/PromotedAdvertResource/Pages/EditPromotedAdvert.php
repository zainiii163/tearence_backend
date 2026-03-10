<?php

namespace App\Filament\Resources\PromotedAdvertResource\Pages;

use App\Filament\Resources\PromotedAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotedAdvert extends EditRecord
{
    protected static string $resource = PromotedAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
