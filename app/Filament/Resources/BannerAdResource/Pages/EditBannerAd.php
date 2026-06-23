<?php

namespace App\Filament\Resources\BannerAdResource\Pages;

use App\Filament\Resources\BannerAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerAd extends EditRecord
{
    protected static string $resource = BannerAdResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['banner_image'])) {
            $data['banner_image'] = BannerAdResource::fileUploadPath(
                $data['banner_image'],
                'banner-images'
            );
        }

        if (! empty($data['business_logo'])) {
            $data['business_logo'] = BannerAdResource::fileUploadPath(
                $data['business_logo'],
                'business-logos'
            );
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['banner_image'])) {
            $data['banner_image'] = BannerAdResource::normalizeUploadFilename($data['banner_image']);
        }

        if (isset($data['business_logo'])) {
            $data['business_logo'] = BannerAdResource::normalizeUploadFilename($data['business_logo']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
