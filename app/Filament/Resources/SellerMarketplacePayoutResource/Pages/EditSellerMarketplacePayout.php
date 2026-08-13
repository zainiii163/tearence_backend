<?php

namespace App\Filament\Resources\SellerMarketplacePayoutResource\Pages;

use App\Filament\Resources\SellerMarketplacePayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSellerMarketplacePayout extends EditRecord
{
    protected static string $resource = SellerMarketplacePayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
