<?php

namespace App\Filament\Resources\CandidateProfileResource\Pages;

use App\Filament\Resources\CandidateProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCandidateProfile extends ViewRecord
{
    protected static string $resource = CandidateProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

