<?php

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Filament\Resources\AffiliateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliate extends EditRecord
{
    protected static string $resource = AffiliateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['pricing_plan_id'], $data['plan_info']);

        if (isset($data['image_url']) && is_array($data['image_url'])) {
            $data['image_url'] = array_values($data['image_url'])[0] ?? null;
        }

        return $data;
    }
}
