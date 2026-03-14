<?php

namespace App\Filament\Resources\AffiliateApplicationResource\Pages;

use App\Filament\Resources\AffiliateApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateApplication extends EditRecord
{
    protected static string $resource = AffiliateApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
