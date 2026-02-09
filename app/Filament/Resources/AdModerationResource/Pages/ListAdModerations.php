<?php

namespace App\Filament\Resources\AdModerationResource\Pages;

use App\Filament\Resources\AdModerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListAdModerations extends ListRecords
{
    protected static string $resource = AdModerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('detect_harmful')
                ->label('Detect Harmful Content')
                ->icon('heroicon-o-shield-exclamation')
                ->color('warning')
                ->action(function () {
                    // This would call the harmful content detection
                    Notification::make()
                        ->success()
                        ->title('Harmful content detection initiated')
                        ->send();
                }),
            Actions\Action::make('cleanup_old')
                ->label('Cleanup Old Ads')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->action(function () {
                    $deletedCount = \App\Models\Listing::where('created_at', '<', now()->subDays(21))->delete();
                    Notification::make()
                        ->success()
                        ->title("Deleted {$deletedCount} old ads")
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }
}
