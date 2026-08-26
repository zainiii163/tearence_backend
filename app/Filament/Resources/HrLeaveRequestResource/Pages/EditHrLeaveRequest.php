<?php

namespace App\Filament\Resources\HrLeaveRequestResource\Pages;

use App\Filament\Resources\HrLeaveRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrLeaveRequest extends EditRecord
{
    protected static string $resource = HrLeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
