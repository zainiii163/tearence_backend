<?php

namespace App\Filament\Resources\FleetManagementResource\Pages;

use App\Filament\Resources\FleetManagementResource;
use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFleetManagement extends EditRecord
{
    protected static string $resource = FleetManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('fullEdit')
                ->label('Edit full listing')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => VehicleResource::getUrl('edit', ['record' => $this->record])),
            Actions\Action::make('back')
                ->label('Back to fleet')
                ->url(FleetManagementResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return FleetManagementResource::getUrl('index');
    }
}
