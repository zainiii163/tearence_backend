<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['destination_url'])) {
            $data['url_link'] = strlen($data['destination_url']) > 100
                ? substr($data['destination_url'], 0, 100)
                : $data['destination_url'];
        }

        if (! empty($data['banner_size'])) {
            $data['size_img'] = $data['banner_size'];
        }

        if (isset($data['status'])) {
            $data['status'] = Banner::normalizeStatus($data['status']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
