<?php

namespace App\Filament\Resources\BannerAdResource\Pages;

use App\Filament\Resources\BannerAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerAd extends EditRecord
{
    protected static string $resource = BannerAdResource::class;

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
