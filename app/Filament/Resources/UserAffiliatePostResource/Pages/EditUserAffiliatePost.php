<?php

namespace App\Filament\Resources\UserAffiliatePostResource\Pages;

use App\Filament\Resources\UserAffiliatePostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserAffiliatePost extends EditRecord
{
    protected static string $resource = UserAffiliatePostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
