<?php

namespace App\Filament\Resources\JobApplicationResource\Pages;

use App\Filament\Resources\JobApplicationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

class CreateJobApplication extends CreateRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['job_id']) && Schema::hasColumn('job_applications', 'job_listing_id')) {
            $data['job_listing_id'] = $data['job_id'];
        }
        if (isset($data['job_id']) && ! Schema::hasColumn('job_applications', 'job_id')) {
            unset($data['job_id']);
        }

        $data['applied_at'] = $data['applied_at'] ?? now();

        return collect($data)
            ->filter(fn ($value, $key) => Schema::hasColumn('job_applications', (string) $key))
            ->all();
    }
}
