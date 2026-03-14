<?php

namespace App\Filament\Resources\SponsoredAdvertResource\Pages;

use App\Filament\Resources\SponsoredAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSponsoredAdverts extends ListRecords
{
    protected static string $resource = SponsoredAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
