<?php

namespace App\Filament\Resources\HrPayrollRecordResource\Pages;

use App\Filament\Resources\HrPayrollRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrPayrollRecord extends EditRecord
{
    protected static string $resource = HrPayrollRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
