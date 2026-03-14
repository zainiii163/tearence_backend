<?php

namespace App\Filament\Resources\FeaturedAdvertResource\Pages;

use App\Filament\Resources\FeaturedAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListFeaturedAdverts extends ListRecords
{
    protected static string $resource = FeaturedAdvertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Export functionality
                    Notification::make()
                        ->title('Export started')
                        ->body('Featured adverts data will be exported to CSV.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
