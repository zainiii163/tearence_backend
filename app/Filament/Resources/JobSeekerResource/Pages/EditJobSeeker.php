<?php

namespace App\Filament\Resources\JobSeekerResource\Pages;

use App\Filament\Resources\JobSeekerResource;
use App\Support\JobSeekerSchema;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditJobSeeker extends EditRecord
{
    protected static string $resource = JobSeekerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(JobSeekerSchema::filterPayload($data));

        return $record;
    }
}
