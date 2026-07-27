<?php

namespace App\Filament\Resources\BusinessTemplateResource\Pages;

use App\Filament\Resources\BusinessTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessTemplates extends ListRecords
{
    protected static string $resource = BusinessTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('pricing')
                ->label('Premium pricing')
                ->icon('heroicon-o-currency-dollar')
                ->url(fn () => \App\Filament\Pages\TemplatePricingSettings::getUrl()),
        ];
    }
}
