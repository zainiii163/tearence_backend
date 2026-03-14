<?php

namespace App\Filament\Resources\UserAffiliatePostResource\Pages;

use App\Filament\Resources\UserAffiliatePostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserAffiliatePosts extends ListRecords
{
    protected static string $resource = UserAffiliatePostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
