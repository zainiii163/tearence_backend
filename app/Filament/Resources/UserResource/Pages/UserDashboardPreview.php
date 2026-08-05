<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\FeaturedAdvert;
use App\Models\SponsoredAdvert;
use App\Models\User;
use App\Models\UserAffiliatePost;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Schema;

class UserDashboardPreview extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'User Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    public function getTitle(): string
    {
        return 'Dashboard — ' . ($this->record->name ?? 'User');
    }

    public function getHeading(): string
    {
        return $this->getTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToProfile')
                ->label('Back to user')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => UserResource::getUrl('view', ['record' => $this->record])),
            Actions\EditAction::make(),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return (bool) ($user->is_super_admin || $user->can_manage_users || $user->can_manage_dashboard);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $stats = static::collectStats($this->record);

        return $infolist
            ->schema([
                Infolists\Components\Section::make('Account')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->label('Name')->weight('bold'),
                        Infolists\Components\TextEntry::make('email')->copyable(),
                        Infolists\Components\TextEntry::make('group_label')
                            ->label('Team / Role')
                            ->state(fn ($record) => $record->group?->fullLabel() ?? '—')
                            ->badge(),
                        Infolists\Components\IconEntry::make('is_active')->label('Active')->boolean(),
                    ]),

                Infolists\Components\Section::make('Listings snapshot')
                    ->description('Counts from the live marketplace for this user (Filament-only — no session impersonation).')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('buy_sell_total')
                            ->label('Buy & Sell total')
                            ->state($stats['buy_sell_total']),
                        Infolists\Components\TextEntry::make('buy_sell_active')
                            ->label('Buy & Sell active')
                            ->state($stats['buy_sell_active']),
                        Infolists\Components\TextEntry::make('buy_sell_expired')
                            ->label('Buy & Sell expired')
                            ->state($stats['buy_sell_expired']),
                        Infolists\Components\TextEntry::make('promoted_total')
                            ->label('Promoted ads')
                            ->state($stats['promoted_total']),
                        Infolists\Components\TextEntry::make('sponsored_total')
                            ->label('Sponsored ads')
                            ->state($stats['sponsored_total']),
                        Infolists\Components\TextEntry::make('featured_total')
                            ->label('Featured (by email)')
                            ->state($stats['featured_total']),
                        Infolists\Components\TextEntry::make('affiliate_total')
                            ->label('Affiliate posts')
                            ->state($stats['affiliate_total']),
                        Infolists\Components\TextEntry::make('affiliate_active')
                            ->label('Affiliate active')
                            ->state($stats['affiliate_active']),
                        Infolists\Components\TextEntry::make('communities')
                            ->label('Communities')
                            ->state($stats['communities']),
                        Infolists\Components\TextEntry::make('saved')
                            ->label('Saved ads')
                            ->state($stats['saved']),
                        Infolists\Components\TextEntry::make('post_usage')
                            ->label('Post quota')
                            ->state($stats['post_usage']),
                        Infolists\Components\TextEntry::make('last_post_at')
                            ->label('Last post')
                            ->dateTime()
                            ->placeholder('Never'),
                    ]),

                Infolists\Components\Section::make('Recent Buy & Sell')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('recent_buy_sell')
                            ->label('')
                            ->state($stats['recent_buy_sell'])
                            ->schema([
                                Infolists\Components\TextEntry::make('title')->weight('bold'),
                                Infolists\Components\TextEntry::make('status')->badge(),
                                Infolists\Components\TextEntry::make('expires_at')->dateTime()->placeholder('—'),
                                Infolists\Components\TextEntry::make('created_at')->dateTime()->label('Posted'),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ])
                    ->visible(fn () => count($stats['recent_buy_sell']) > 0),

                Infolists\Components\Section::make('Recent affiliate posts')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('recent_affiliates')
                            ->label('')
                            ->state($stats['recent_affiliates'])
                            ->schema([
                                Infolists\Components\TextEntry::make('title')->weight('bold'),
                                Infolists\Components\TextEntry::make('status')->badge(),
                                Infolists\Components\IconEntry::make('is_active')->boolean()->label('Active'),
                                Infolists\Components\TextEntry::make('created_at')->dateTime()->label('Posted'),
                            ])
                            ->columns(4)
                            ->contained(false),
                    ])
                    ->visible(fn () => count($stats['recent_affiliates']) > 0),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function collectStats(User $user): array
    {
        $buySellTotal = 0;
        $buySellActive = 0;
        $buySellExpired = 0;
        $recentBuySell = [];
        $promoted = 0;
        $sponsored = 0;
        $featured = 0;
        $affiliateTotal = 0;
        $affiliateActive = 0;
        $recentAffiliates = [];
        $communities = 0;
        $saved = 0;

        try {
            $buySellTotal = (int) $user->buySellAdverts()->count();
            $buySellActive = (int) $user->buySellAdverts()->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->count();
            $buySellExpired = (int) $user->buySellAdverts()
                ->where(function ($q) {
                    $q->where('status', 'expired')
                        ->orWhere(fn ($q2) => $q2->whereNotNull('expires_at')->where('expires_at', '<=', now()));
                })->count();
            $recentBuySell = $user->buySellAdverts()
                ->latest('created_at')
                ->limit(8)
                ->get(['title', 'status', 'expires_at', 'created_at'])
                ->map(fn ($row) => $row->toArray())
                ->all();
        } catch (\Throwable $e) {
            // relation / table may be unavailable
        }

        try {
            $promoted = (int) $user->promotedAdverts()->count();
        } catch (\Throwable $e) {
        }

        try {
            if (Schema::hasTable('sponsored_adverts')) {
                $sponsored = (int) SponsoredAdvert::query()
                    ->where('created_by', $user->user_id)
                    ->count();
            }
        } catch (\Throwable $e) {
        }

        try {
            if (Schema::hasTable('featured_adverts') && $user->email) {
                $featured = (int) FeaturedAdvert::query()
                    ->where('contact_email', $user->email)
                    ->count();
            }
        } catch (\Throwable $e) {
        }

        try {
            if (Schema::hasTable((new UserAffiliatePost)->getTable())) {
                $q = UserAffiliatePost::query()->where('user_id', $user->user_id);
                $affiliateTotal = (int) (clone $q)->count();
                $affiliateActive = (int) (clone $q)->where('is_active', true)->count();
                $recentAffiliates = (clone $q)
                    ->latest('created_at')
                    ->limit(8)
                    ->get(['title', 'status', 'is_active', 'created_at'])
                    ->map(fn ($row) => $row->toArray())
                    ->all();
            }
        } catch (\Throwable $e) {
        }

        try {
            $communities = (int) $user->communityMemberships()->count();
        } catch (\Throwable $e) {
        }

        try {
            $saved = (int) $user->buySellSavedAdverts()->count();
        } catch (\Throwable $e) {
        }

        $used = $user->post_count ?? $user->posts_count ?? 0;
        $limit = $user->posting_limit ?? $user->posts_limit ?? '—';

        return [
            'buy_sell_total' => $buySellTotal,
            'buy_sell_active' => $buySellActive,
            'buy_sell_expired' => $buySellExpired,
            'promoted_total' => $promoted,
            'sponsored_total' => $sponsored,
            'featured_total' => $featured,
            'affiliate_total' => $affiliateTotal,
            'affiliate_active' => $affiliateActive,
            'communities' => $communities,
            'saved' => $saved,
            'post_usage' => "{$used} / {$limit}",
            'recent_buy_sell' => $recentBuySell,
            'recent_affiliates' => $recentAffiliates,
        ];
    }
}
