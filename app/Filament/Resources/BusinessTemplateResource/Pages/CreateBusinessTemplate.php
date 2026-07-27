<?php

namespace App\Filament\Resources\BusinessTemplateResource\Pages;

use App\Filament\Resources\BusinessTemplateResource;
use App\Models\BusinessTemplate;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessTemplate extends CreateRecord
{
    protected static string $resource = BusinessTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['slug'])) {
            $data['slug'] = BusinessTemplate::makeSlug(
                $data['title'] ?? 'template',
                $data['vertical'] ?? 'business',
                $data['category_slug'] ?? 'default'
            );
        }

        $data['is_catalog'] = $data['is_catalog'] ?? true;

        return $data;
    }
}
