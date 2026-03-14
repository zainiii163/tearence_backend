<?php

namespace App\Filament\Resources\FundingProjectResource\Pages;

use App\Filament\Resources\FundingProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFundingProjects extends ListRecords
{
    protected static string $resource = FundingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
