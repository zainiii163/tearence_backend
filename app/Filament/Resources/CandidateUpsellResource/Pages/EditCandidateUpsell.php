<?php

namespace App\Filament\Resources\CandidateUpsellResource\Pages;

use App\Filament\Resources\CandidateUpsellResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCandidateUpsell extends EditRecord
{
    protected static string $resource = CandidateUpsellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

