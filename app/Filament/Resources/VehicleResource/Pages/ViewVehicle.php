<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected static ?string $title = 'Vehicle Details';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                    $this->notify('success', 'Vehicle approved successfully');
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),
            
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['status' => 'rejected']);
                    $this->notify('success', 'Vehicle rejected');
                })
                ->visible(fn (): bool => $this->record->status === 'pending'),
            
            Actions\Action::make('toggle_active')
                ->label(fn (): string => $this->record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (): string => $this->record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn (): string => $this->record->is_active ? 'warning' : 'success')
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    $this->notify('success', 'Vehicle status updated');
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            VehicleStatsWidget::class,
        ];
    }
}
