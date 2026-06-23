<?php

namespace App\Filament\Resources\SponsoredAdvertResource\Pages;

use App\Filament\Resources\SponsoredAdvertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSponsoredAdvert extends CreateRecord
{
    protected static string $resource = SponsoredAdvertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return SponsoredAdvertResource::prepareMediaForSave($data);
    }
}
