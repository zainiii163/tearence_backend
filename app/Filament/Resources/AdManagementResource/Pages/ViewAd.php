<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Contracts\View\View;

class ViewAd extends ViewRecord
{
    protected static string $resource = AdManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            
            Actions\Action::make('mark_paid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'payment_status' => 'paid',
                        'is_active' => true,
                    ]);
                })
                ->visible(fn ($record) => $record->payment_status === 'pending'),
            
            Actions\Action::make('toggle_status')
                ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                ->action(function ($record) {
                    $record->update(['is_active' => !$record->is_active]);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Advertisement Details')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('title')
                                    ->columnSpan(2),
                                Components\BadgeEntry::make('type')
                                    ->colors([
                                        'primary' => 'banner',
                                        'success' => 'sponsored',
                                        'warning' => 'featured',
                                    ])
                                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                            ]),
                        
                        Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        
                        Components\Grid::make(2)
                            ->schema([
                                Components\ImageEntry::make('image')
                                    ->square()
                                    ->height(200)
                                    ->defaultImageUrl(url('/placeholder.png')),
                                
                                Components\Grid::make(1)
                                    ->schema([
                                        Components\TextEntry::make('url')
                                            ->url()
                                            ->icon('heroicon-o-link'),
                                        Components\TextEntry::make('pricingPlan.name')
                                            ->label('Pricing Plan'),
                                        Components\TextEntry::make('price')
                                            ->money('USD'),
                                        Components\BadgeEntry::make('payment_status')
                                            ->colors([
                                                'secondary' => 'pending',
                                                'success' => 'paid',
                                                'danger' => 'failed',
                                                'warning' => 'refunded',
                                            ])
                                            ->formatStateUsing(fn ($state) => ucfirst($state)),
                                    ]),
                            ]),
                    ]),
                
                Components\Section::make('Schedule & Status')
                    ->schema([
                        Components\Grid::make(4)
                            ->schema([
                                Components\TextEntry::make('start_date')
                                    ->dateTime(),
                                Components\TextEntry::make('end_date')
                                    ->dateTime(),
                                Components\IconEntry::make('is_active')
                                    ->boolean(),
                                Components\TextEntry::make('created_at')
                                    ->dateTime()
                                    ->label('Created'),
                            ]),
                    ]),
            ]);
    }

    public function getFooter(): ?View
    {
        return view('filament.resources.ad-management.pages.footer', [
            'record' => $this->record,
        ]);
    }
}
