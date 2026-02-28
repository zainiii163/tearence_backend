<?php

namespace App\Filament\Resources\AdManagementResource\Pages;

use App\Filament\Resources\AdManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
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
                Section::make('Advertisement Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->columnSpan(2),
                                BadgeEntry::make('type')
                                    ->colors([
                                        'primary' => 'banner',
                                        'success' => 'sponsored',
                                        'warning' => 'featured',
                                    ])
                                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                            ]),
                        
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('image')
                                    ->square()
                                    ->height(200)
                                    ->defaultImageUrl(url('/placeholder.png'))
                                    ->getStateUsing(function ($record) {
                                        if (!$record->image) {
                                            return null;
                                        }
                                        
                                        // If the image path is already a full URL, return it as is
                                        if (filter_var($record->image, FILTER_VALIDATE_URL)) {
                                            return $record->image;
                                        }
                                        
                                        // If the image path starts with 'advertisements/', make sure it's properly formatted
                                        if (str_starts_with($record->image, 'advertisements/')) {
                                            return $record->image;
                                        }
                                        
                                        // If it's just a filename, prepend the directory
                                        return 'advertisements/' . $record->image;
                                    }),
                                
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('url')
                                            ->url()
                                            ->icon('heroicon-o-link'),
                                        TextEntry::make('pricingPlan.name')
                                            ->label('Pricing Plan'),
                                        TextEntry::make('price')
                                            ->money('USD'),
                                        BadgeEntry::make('payment_status')
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
                
                Section::make('Schedule & Status')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->dateTime(),
                                TextEntry::make('end_date')
                                    ->dateTime(),
                                IconEntry::make('is_active')
                                    ->boolean(),
                                TextEntry::make('created_at')
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
