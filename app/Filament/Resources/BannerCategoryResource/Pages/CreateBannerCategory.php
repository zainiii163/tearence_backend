<?php

namespace App\Filament\Resources\BannerCategoryResource\Pages;

use App\Filament\Resources\BannerCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerCategory extends CreateRecord
{
    protected static string $resource = BannerCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
