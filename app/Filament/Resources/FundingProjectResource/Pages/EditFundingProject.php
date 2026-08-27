<?php

namespace App\Filament\Resources\FundingProjectResource\Pages;

use App\Filament\Resources\FundingProjectResource;
use App\Support\FundingProjectSchema;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFundingProject extends EditRecord
{
    protected static string $resource = FundingProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(FundingProjectSchema::normalizeAdminPayload($data));

        return $record;
    }
}
