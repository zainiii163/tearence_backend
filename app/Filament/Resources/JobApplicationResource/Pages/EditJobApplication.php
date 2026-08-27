<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Filament\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Schema;

class EditJobApplication extends EditRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['job_id']) && Schema::hasColumn('job_applications', 'job_listing_id')) {
            $data['job_listing_id'] = $data['job_id'];
        }
        if (isset($data['job_id']) && ! Schema::hasColumn('job_applications', 'job_id')) {
            unset($data['job_id']);
        }

        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn('job_applications', (string) $key))
            ->all();
    }
}
