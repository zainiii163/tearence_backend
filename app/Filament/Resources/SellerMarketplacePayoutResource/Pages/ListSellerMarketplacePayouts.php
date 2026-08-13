<?php

namespace App\Filament\Resources\SellerMarketplacePayoutResource\Pages;

use App\Filament\Resources\SellerMarketplacePayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellerMarketplacePayouts extends ListRecords
{
    protected static string $resource = SellerMarketplacePayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
