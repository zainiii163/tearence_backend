<?php

namespace App\Filament\Resources\HrLeaveRequestResource\Pages;

use App\Filament\Resources\HrLeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHrLeaveRequests extends ListRecords
{
    protected static string $resource = HrLeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
