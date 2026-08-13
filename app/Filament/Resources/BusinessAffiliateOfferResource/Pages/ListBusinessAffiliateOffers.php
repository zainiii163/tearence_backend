<?php

namespace App\Filament\Resources\BusinessAffiliateOfferResource\Pages;

use App\Filament\Resources\BusinessAffiliateOfferResource;
use App\Filament\Widgets\AffiliateOverviewWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessAffiliateOffers extends ListRecords
{
    protected static string $resource = BusinessAffiliateOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AffiliateOverviewWidget::class,
        ];
    }
}
