<?php

namespace App\Filament\Resources\SponsoredCategoryResource\Pages;

use App\Filament\Resources\SponsoredCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsoredCategory extends EditRecord
{
    protected static string $resource = SponsoredCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
