<?php

namespace App\Filament\Resources\FeaturedAdvertResource\Pages;

use App\Filament\Resources\FeaturedAdvertResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFeaturedAdvert extends CreateRecord
{
    protected static string $resource = FeaturedAdvertResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']) . '-' . uniqid();
        }

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
