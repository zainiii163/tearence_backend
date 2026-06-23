<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (! empty($data['destination_url'])) {
            $data['url_link'] = strlen($data['destination_url']) > 100
                ? substr($data['destination_url'], 0, 100)
                : $data['destination_url'];
        }

        if (! empty($data['banner_size'])) {
            $data['size_img'] = $data['banner_size'];
        }

        $data['status'] = Banner::normalizeStatus($data['status'] ?? 'active');
        $data['is_active'] = $data['status'] === 'active';

        $expiresAt = $data['expires_at'] ?? null;
        if (empty($expiresAt) || Carbon::parse($expiresAt)->lte(now())) {
            $data['expires_at'] = now()->addDays(30);
        }

        return $data;
    }
}
