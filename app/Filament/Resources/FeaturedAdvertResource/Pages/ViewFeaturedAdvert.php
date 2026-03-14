<?php

namespace App\Filament\Resources\FeaturedAdvertResource\Pages;

use App\Filament\Resources\FeaturedAdvertResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Actions\Action;

class ViewFeaturedAdvert extends ViewRecord
{
    protected static string $resource = FeaturedAdvertResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->columnSpanFull(),

                                TextEntry::make('description')
                                    ->label('Description')
                                    ->columnSpanFull()
                                    ->markdown(),

                                BadgeEntry::make('advert_type')
                                    ->label('Advert Type')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'product' => 'Product / Item for Sale',
                                        'service' => 'Service / Business Offer',
                                        'property' => 'Property / Real Estate',
                                        'job' => 'Job / Recruitment',
                                        'event' => 'Event / Experience',
                                        'vehicle' => 'Vehicles / Motors',
                                        'business' => 'Business Opportunity',
                                        'education' => 'Education / Course',
                                        'travel' => 'Travel / Experience',
                                        'fashion' => 'Fashion / Beauty',
                                        'electronics' => 'Electronics',
                                        'pets' => 'Pets / Animals',
                                        'home' => 'Home / Garden',
                                        'health' => 'Health / Wellness',
                                        'misc' => 'Miscellaneous / Other',
                                        default => $state,
                                    }),

                                BadgeEntry::make('condition')
                                    ->label('Condition')
                                    ->colors([
                                        'success' => 'new',
                                        'warning' => 'used',
                                        'info' => 'refurbished',
                                    ]),
                            ]),
                    ]),

                Section::make('Pricing & Payment')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('formatted_price')
                                    ->label('Price')
                                    ->money('GBP'),

                                TextEntry::make('currency')
                                    ->label('Currency')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'GBP' => 'GBP (£)',
                                        'USD' => 'USD ($)',
                                        'EUR' => 'EUR (€)',
                                        default => $state,
                                    }),

                                BadgeEntry::make('payment_status')
                                    ->label('Payment Status')
                                    ->colors([
                                        'warning' => 'pending',
                                        'success' => 'paid',
                                        'danger' => 'failed',
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                BadgeEntry::make('upsell_tier')
                                    ->label('Upsell Tier')
                                    ->colors([
                                        'warning' => 'promoted',
                                        'success' => 'featured',
                                        'danger' => 'sponsored',
                                    ])
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'promoted' => 'Promoted',
                                        'featured' => 'Featured',
                                        'sponsored' => 'Sponsored',
                                        default => $state,
                                    }),

                                TextEntry::make('upsell_price')
                                    ->label('Upsell Price')
                                    ->money('GBP'),
                            ]),
                    ]),

                Section::make('Contact Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('contact_name')
                                    ->label('Contact Name')
                                    ->copyable(),

                                TextEntry::make('contact_email')
                                    ->label('Contact Email')
                                    ->copyable(),

                                TextEntry::make('contact_phone')
                                    ->label('Contact Phone')
                                    ->copyable(),

                                TextEntry::make('website')
                                    ->label('Website')
                                    ->url(fn($state) => $state)
                                    ->openUrlInNewTab(),
                            ]),
                    ]),

                Section::make('Location')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('country')
                                    ->label('Country'),

                                TextEntry::make('city')
                                    ->label('City'),

                                TextEntry::make('latitude')
                                    ->label('Latitude')
                                    ->formatStateUsing(fn($state) => $state ? number_format($state, 8) : 'N/A'),

                                TextEntry::make('longitude')
                                    ->label('Longitude')
                                    ->formatStateUsing(fn($state) => $state ? number_format($state, 8) : 'N/A'),
                            ]),
                    ]),

                Section::make('Media')
                    ->schema([
                        RepeatableEntry::make('images')
                            ->label('Images')
                            ->columnSpanFull()
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('')
                                    ->height(200)
                                    ->width(300)
                                    ->disk('public')
                            ])
                            ->columns(3),

                        TextEntry::make('video_url')
                            ->label('Video URL')
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->copyable(),
                    ])
                    ->collapsible(),

                Section::make('Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('starts_at')
                                    ->label('Starts At')
                                    ->dateTime(),

                                TextEntry::make('expires_at')
                                    ->label('Expires At')
                                    ->dateTime(),
                            ]),
                    ]),

                Section::make('Statistics')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('view_count')
                                    ->label('Views')
                                    ->numeric()
                                    ->alignCenter(),

                                TextEntry::make('save_count')
                                    ->label('Saves')
                                    ->numeric()
                                    ->alignCenter(),

                                TextEntry::make('contact_count')
                                    ->label('Contacts')
                                    ->numeric()
                                    ->alignCenter(),

                                TextEntry::make('rating')
                                    ->label('Rating')
                                    ->numeric()
                                    ->alignCenter()
                                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : 'N/A'),
                            ]),
                    ]),

                Section::make('Admin Settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),

                                IconEntry::make('is_verified_seller')
                                    ->label('Verified Seller')
                                    ->boolean(),
                            ]),

                        TextEntry::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),

                Section::make('System Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('activate')
                ->label('Activate')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn($record): bool => !$record->is_active)
                ->action(function ($record) {
                    $record->update(['is_active' => true]);
                    \Filament\Notifications\Notification::make()
                        ->title('Featured advert activated')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('deactivate')
                ->label('Deactivate')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn($record): bool => $record->is_active)
                ->action(function ($record) {
                    $record->update(['is_active' => false]);
                    \Filament\Notifications\Notification::make()
                        ->title('Featured advert deactivated')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('mark_paid')
                ->label('Mark as Paid')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn($record): bool => $record->payment_status === 'pending')
                ->action(function ($record) {
                    $record->update(['payment_status' => 'paid']);
                    \Filament\Notifications\Notification::make()
                        ->title('Payment status updated')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('verify_seller')
                ->label('Verify Seller')
                ->icon('heroicon-o-shield-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn($record): bool => !$record->is_verified_seller)
                ->action(function ($record) {
                    $record->update(['is_verified_seller' => true]);
                    \Filament\Notifications\Notification::make()
                        ->title('Seller verified')
                        ->success()
                        ->send();
                }),
        ];
    }
}
