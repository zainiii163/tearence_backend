<?php

namespace App\Filament\Resources\PromotedAdvertCategoryResource\Pages;

use App\Filament\Resources\PromotedAdvertCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPromotedAdvertCategory extends ViewRecord
{
    protected static string $resource = PromotedAdvertCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
