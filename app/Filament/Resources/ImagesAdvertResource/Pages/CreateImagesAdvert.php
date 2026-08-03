<?php

namespace App\Filament\Resources\ImagesAdvertResource\Pages;

use App\Filament\Resources\ImagesAdvertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateImagesAdvert extends CreateRecord
{
    protected static string $resource = ImagesAdvertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $data['thumbnail'] = $data['thumbnail'] ?? ($data['main_image'] ?? null);
        $data['images'] = $data['images'] ?? (isset($data['main_image']) ? [$data['main_image']] : []);
        $data['verification_status'] = $data['verification_status'] ?? 'verified';
        $data['verified_at'] = ($data['verification_status'] ?? null) === 'verified' ? now() : null;
        $data['verified_by'] = ($data['verification_status'] ?? null) === 'verified' ? auth()->id() : null;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_verified_creator'] = $data['is_verified_creator'] ?? true;
        $data['views_count'] = 0;
        $data['downloads_count'] = 0;
        $data['saves_count'] = 0;
        $data['rating'] = 0;
        $data['rating_count'] = 0;
        $data['has_model_release'] = $data['has_model_release'] ?? false;
        $data['has_property_release'] = $data['has_property_release'] ?? false;
        $data['media_type'] = $data['media_type'] ?? 'image';

        return $data;
    }
}
