<?php

namespace App\Filament\Resources\PromotedAdvertResource\Pages;

use App\Filament\Resources\PromotedAdvertResource;
use App\Models\PromotedAdvert;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManagePromotedAdverts extends ManageRecords
{
    protected static string $resource = PromotedAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_selected')
                ->label('Approve Selected')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function (array $records) {
                    foreach ($records as $record) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                        ]);
                    }
                    $this->notify('success', 'Selected adverts approved successfully');
                })
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->isAdmin()),

            Action::make('reject_selected')
                ->label('Reject Selected')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function (array $records) {
                    foreach ($records as $record) {
                        $record->update(['status' => 'rejected']);
                    }
                    $this->notify('success', 'Selected adverts rejected successfully');
                })
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->isAdmin()),

            Action::make('feature_selected')
                ->label('Feature Selected')
                ->icon('heroicon-o-star')
                ->color('warning')
                ->action(function (array $records) {
                    foreach ($records as $record) {
                        $record->update(['is_featured' => true]);
                    }
                    $this->notify('success', 'Selected adverts featured successfully');
                })
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->isAdmin()),

            Action::make('export_data')
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    // Export logic here
                    $this->notify('info', 'Export functionality would be implemented here');
                })
                ->visible(fn () => auth()->user()->isAdmin()),

            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // If user is not admin, only show their own adverts
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
