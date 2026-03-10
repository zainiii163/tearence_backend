<?php

namespace App\Filament\Resources\PromotedAdvertResource\Pages;

use App\Filament\Resources\PromotedAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPromotedAdvert extends ViewRecord
{
    protected static string $resource = PromotedAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
