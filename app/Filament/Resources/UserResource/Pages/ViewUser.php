<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact & profile')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\ImageEntry::make('avatar')
                            ->circular()
                            ->columnSpan(1),
                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Full name')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('user_uid')
                                ->label('User UID')
                                ->copyable(),
                            Infolists\Components\TextEntry::make('group.name')
                                ->label('User role')
                                ->badge(),
                        ])->columnSpan(2),
                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->columnSpan(1),
                        Infolists\Components\TextEntry::make('mobile_number')
                            ->label('Phone')
                            ->icon('heroicon-m-phone')
                            ->placeholder('—')
                            ->copyable()
                            ->columnSpan(1),
                        Infolists\Components\TextEntry::make('timezone')
                            ->placeholder('—')
                            ->columnSpan(1),
                        Infolists\Components\TextEntry::make('address')
                            ->label('Address')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('—')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('city')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('country')
                            ->placeholder('—'),
                    ]),

                Infolists\Components\Section::make('Account status')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_super_admin')
                            ->label('Super admin')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('email_verified')
                            ->label('Email verified')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('mobile_verified')
                            ->label('Mobile verified')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('kyc_status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'verified' => 'success',
                                'submitted' => 'info',
                                'rejected' => 'danger',
                                default => 'warning',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Registered')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('last_post_at')
                            ->label('Last post')
                            ->dateTime()
                            ->placeholder('Never'),
                        Infolists\Components\TextEntry::make('post_count')
                            ->label('Posts used')
                            ->formatStateUsing(fn ($record) => ($record->post_count ?? $record->posts_count ?? 0) . ' / ' . ($record->posting_limit ?? $record->posts_limit ?? '—')),
                    ]),

                Infolists\Components\Section::make('Backend permissions')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\IconEntry::make('can_manage_users')->boolean(),
                        Infolists\Components\IconEntry::make('can_manage_categories')->boolean(),
                        Infolists\Components\IconEntry::make('can_manage_listings')->boolean(),
                        Infolists\Components\IconEntry::make('can_manage_dashboard')->boolean(),
                        Infolists\Components\IconEntry::make('can_view_analytics')->boolean(),
                        Infolists\Components\IconEntry::make('is_business_admin')->boolean()->label('Business admin'),
                    ]),

                Infolists\Components\Section::make('Activity on the website')
                    ->description('What this user is signed up for / using')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('buy_sell_count')
                            ->label('Buy & Sell ads')
                            ->state(function ($record) {
                                try {
                                    return (int) $record->buySellAdverts()->count();
                                } catch (\Throwable $e) {
                                    return 0;
                                }
                            }),
                        Infolists\Components\TextEntry::make('promoted_count')
                            ->label('Promoted ads')
                            ->state(function ($record) {
                                try {
                                    return (int) $record->promotedAdverts()->count();
                                } catch (\Throwable $e) {
                                    return 0;
                                }
                            }),
                        Infolists\Components\TextEntry::make('community_count')
                            ->label('Communities')
                            ->state(function ($record) {
                                try {
                                    return (int) $record->communityMemberships()->count();
                                } catch (\Throwable $e) {
                                    return 0;
                                }
                            }),
                        Infolists\Components\TextEntry::make('saved_count')
                            ->label('Saved Buy & Sell')
                            ->state(function ($record) {
                                try {
                                    return (int) $record->buySellSavedAdverts()->count();
                                } catch (\Throwable $e) {
                                    return 0;
                                }
                            }),
                    ]),
            ]);
    }
}
