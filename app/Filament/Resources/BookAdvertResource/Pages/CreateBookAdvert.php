<?php

namespace App\Filament\Resources\BookAdvertResource\Pages;

use App\Filament\Resources\BookAdvertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookAdvert extends CreateRecord
{
    protected static string $resource = BookAdvertResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['is_free']) || (isset($data['price']) && (float) $data['price'] <= 0)) {
            $data['price'] = 0;
            $data['is_free'] = true;
        }

        // Admin uploads publish immediately unless explicitly set otherwise
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        if (empty($data['content_kind'])) {
            $data['content_kind'] = 'book';
        }

        return $data;
    }
}
