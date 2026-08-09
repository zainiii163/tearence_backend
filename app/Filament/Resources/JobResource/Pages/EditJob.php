<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditJob extends EditRecord
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug((string) $data['title']).'-'.time();
        }

        if (($data['status'] ?? '') === 'active') {
            $data['is_active'] = true;
        }
        if (in_array($data['status'] ?? '', ['draft', 'rejected', 'expired'], true)) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
