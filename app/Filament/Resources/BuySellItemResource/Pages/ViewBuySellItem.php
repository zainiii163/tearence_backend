<?php

namespace App\Filament\Resources\BuySellItemResource\Pages;

use App\Filament\Resources\BuySellItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBuySellItem extends ViewRecord
{
    protected static string $resource = BuySellItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.resources.buy-sell-item.pages.view-buy-sell-item', [
            'record' => $this->record,
        ]);
    }
}
