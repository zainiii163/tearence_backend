<?php

namespace App\Filament\Resources\SellerMarketplacePayoutResource\Pages;

use App\Filament\Resources\SellerMarketplacePayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSellerMarketplacePayout extends ViewRecord
{
    protected static string $resource = SellerMarketplacePayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
