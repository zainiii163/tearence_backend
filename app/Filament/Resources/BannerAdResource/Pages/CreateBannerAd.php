<?php

namespace App\Filament\Resources\BannerAdResource\Pages;

use App\Filament\Resources\BannerAdResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerAd extends CreateRecord
{
    protected static string $resource = BannerAdResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Admin-created ads should be publishable on the website by default.
        if (empty($data['status']) || $data['status'] === 'draft') {
            $data['status'] = 'active';
        }

        $data['is_active'] = ($data['status'] ?? '') === 'active';

        if ($data['is_active'] && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }

        $validityEnd = $data['validity_end'] ?? null;
        if ($data['is_active'] && (empty($validityEnd) || Carbon::parse($validityEnd)->lte(now()))) {
            $data['validity_end'] = now()->addDays(30)->toDateString();
        }

        if (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        if (isset($data['banner_image'])) {
            $data['banner_image'] = BannerAdResource::normalizeUploadFilename($data['banner_image']);
        }

        if (isset($data['business_logo'])) {
            $data['business_logo'] = BannerAdResource::normalizeUploadFilename($data['business_logo']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
