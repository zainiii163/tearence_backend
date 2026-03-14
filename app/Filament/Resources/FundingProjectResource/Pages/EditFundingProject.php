<?php

namespace App\Filament\Resources\FundingProjectResource\Pages;

use App\Filament\Resources\FundingProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFundingProject extends EditRecord
{
    protected static string $resource = FundingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
