<?php

namespace App\Filament\Resources\BannerAdResource\Pages;

use App\Filament\Resources\BannerAdResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerAd extends CreateRecord
{
    protected static string $resource = BannerAdResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
