<?php

namespace App\Filament\Resources\VehicleEnquiryResource\Pages;

use App\Filament\Resources\VehicleEnquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleEnquiry extends EditRecord
{
    protected static string $resource = VehicleEnquiryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\Action::make('mark_replied')
                ->label('Mark as Replied')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function () {
                    $this->record->markAsReplied();
                }),
            
            Actions\Action::make('mark_closed')
                ->label('Mark as Closed')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn ($record) => $record->status !== 'closed')
                ->action(function () {
                    $this->record->markAsClosed();
                }),
        ];
    }
}
