<?php

namespace App\Filament\Resources\FundingProjectResource\Pages;

use App\Filament\Resources\FundingProjectResource;
use App\Models\FundingProject;
use App\Support\FundingProjectSchema;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFundingProject extends CreateRecord
{
    protected static string $resource = FundingProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return FundingProject::create(FundingProjectSchema::normalizeAdminPayload($data));
    }
}
