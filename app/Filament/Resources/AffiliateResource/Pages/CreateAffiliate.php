<?php

namespace App\Filament\Resources\AffiliateResource\Pages;

use App\Filament\Resources\AffiliateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAffiliate extends CreateRecord
{
    protected static string $resource = AffiliateResource::class;

    /**
     * Strip virtual fields that are not columns on affiliate_links.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['pricing_plan_id'], $data['plan_info']);

        // Filament FileUpload may leave an array; store a single path string.
        if (isset($data['image_url']) && is_array($data['image_url'])) {
            $data['image_url'] = array_values($data['image_url'])[0] ?? null;
        }

        return $data;
    }
}
