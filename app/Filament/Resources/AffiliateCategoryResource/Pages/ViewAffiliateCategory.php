<?php

namespace App\Filament\Resources\AffiliateCategoryResource\Pages;

use App\Filament\Resources\AffiliateCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAffiliateCategory extends ViewRecord
{
    protected static string $resource = AffiliateCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
