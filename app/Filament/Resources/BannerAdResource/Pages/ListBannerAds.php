<?php

namespace App\Filament\Resources\BannerAdResource\Pages;

use App\Filament\Resources\BannerAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannerAds extends ListRecords
{
    protected static string $resource = BannerAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Banner Ad'),
        ];
    }
}
