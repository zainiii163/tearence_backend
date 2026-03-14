<?php

namespace App\Filament\Resources\FeaturedAdvertResource\Pages;

use App\Filament\Resources\FeaturedAdvertResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditFeaturedAdvert extends EditRecord
{
    protected static string $resource = FeaturedAdvertResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Generate slug if title changed and slug is empty
        if (isset($data['title']) && (empty($data['slug']) || $data['slug'] === $record->slug)) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']) . '-' . uniqid();
        }

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
