<?php

namespace App\Filament\Resources\SponsoredAdvertResource\Pages;

use App\Filament\Resources\SponsoredAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsoredAdvert extends EditRecord
{
    protected static string $resource = SponsoredAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
