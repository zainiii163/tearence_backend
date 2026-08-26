<?php

namespace App\Filament\Resources\HrEmployeeResource\Pages;

use App\Filament\Resources\HrEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrEmployee extends EditRecord
{
    protected static string $resource = HrEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
