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
            Actions\Action::make('userDashboard')
                ->label('Open user dashboard')
                ->icon('heroicon-o-computer-desktop')
                ->color('primary')
                ->url(fn () => UserResource::getUrl('dashboard', ['record' => $this->record])),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $stats = UserDashboardPreview::collectStats($this->record);

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
                            Infolists\Components\TextEntry::make('group_label')
                                ->label('Team / Role')
                                ->state(fn ($record) => $record->group?->fullLabel() ?? '—')
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

                Infolists\Components\Section::make('User Dashboard')
                    ->description('Marketplace activity snapshot — open the full dashboard for recent items.')
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('openDashboard')
                            ->label('Open full dashboard')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->url(fn ($record) => UserResource::getUrl('dashboard', ['record' => $record])),
                    ])
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('dash_buy_sell')
                            ->label('Buy & Sell')
                            ->state($stats['buy_sell_total'] . ' total · ' . $stats['buy_sell_active'] . ' active · ' . $stats['buy_sell_expired'] . ' expired'),
                        Infolists\Components\TextEntry::make('dash_promoted')
                            ->label('Promoted')
                            ->state($stats['promoted_total']),
                        Infolists\Components\TextEntry::make('dash_sponsored')
                            ->label('Sponsored')
                            ->state($stats['sponsored_total']),
                        Infolists\Components\TextEntry::make('dash_featured')
                            ->label('Featured')
                            ->state($stats['featured_total']),
                        Infolists\Components\TextEntry::make('dash_affiliate')
                            ->label('Affiliate posts')
                            ->state($stats['affiliate_total'] . ' (' . $stats['affiliate_active'] . ' active)'),
                        Infolists\Components\TextEntry::make('dash_communities')
                            ->label('Communities')
                            ->state($stats['communities']),
                        Infolists\Components\TextEntry::make('dash_saved')
                            ->label('Saved ads')
                            ->state($stats['saved']),
                        Infolists\Components\TextEntry::make('dash_quota')
                            ->label('Post quota')
                            ->state($stats['post_usage']),
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
            ]);
    }
}
