<?php

namespace App\Filament\Resources\AdModerationResource\Pages;

use App\Filament\Resources\AdModerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAdModeration extends ViewRecord
{
    protected static string $resource = AdModerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->approval_status === 'pending')
                ->form([
                    \Filament\Forms\Components\Select::make('post_type')
                        ->label('Post Type')
                        ->options([
                            'regular' => 'Regular',
                            'sponsored' => 'Sponsored',
                            'promoted' => 'Promoted',
                            'admin' => 'Admin Post',
                        ])
                        ->default('regular')
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $record->approve(auth()->id(), $data['post_type']);
                })
                ->requiresConfirmation(),
        ];
    }
}
