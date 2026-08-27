<?php

namespace App\Filament\Resources\JobSeekerResource\Pages;

use App\Filament\Resources\JobSeekerResource;
use App\Models\JobSeeker;
use App\Support\JobSeekerSchema;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateJobSeeker extends CreateRecord
{
    protected static string $resource = JobSeekerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return JobSeeker::create(JobSeekerSchema::filterPayload($data));
    }
}
