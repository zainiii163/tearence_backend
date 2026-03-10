<?php

namespace App\Filament\Resources\PromotedAdvertCategoryResource\Pages;

use App\Filament\Resources\PromotedAdvertCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotedAdvertCategory extends EditRecord
{
    protected static string $resource = PromotedAdvertCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
