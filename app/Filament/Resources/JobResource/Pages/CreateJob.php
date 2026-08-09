<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = $data['slug'] ?? (Str::slug((string) ($data['title'] ?? 'job')).'-'.time());
        $data['posted_at'] = $data['posted_at'] ?? now();
        $data['expires_at'] = $data['expires_at'] ?? now()->addDays(30);
        $data['status'] = $data['status'] ?? 'active';
        $data['is_active'] = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : (($data['status'] ?? '') === 'active');

        // Keep visibility in sync with status
        if (($data['status'] ?? '') === 'active') {
            $data['is_active'] = true;
        }
        if (in_array($data['status'] ?? '', ['draft', 'rejected', 'expired'], true)) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
