<?php

namespace App\Services;

use App\Filament\Resources\BannerAdResource;
use App\Filament\Resources\BuySellAdvertResource;
use App\Filament\Resources\FeaturedAdvertResource;
use App\Filament\Resources\PromotedAdvertResource;
use App\Filament\Resources\SponsoredAdvertResource;
use App\Filament\Resources\VehicleResource;
use App\Models\BannerAd;
use App\Models\BuySellAdvert;
use App\Models\FeaturedAdvert;
use App\Models\PromotedAdvert;
use App\Models\SponsoredAdvert;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Unified Live / Expiring / Expired / Pending view across advert types.
 */
class AdvertLifecycleService
{
    public const EXPIRING_DAYS = 7;

    /**
     * @return array{counts: array<string,int>, rows: Collection<int, array<string, mixed>>}
     */
    public function snapshot(?string $bucket = null, ?string $type = null): array
    {
        $rows = $this->collectAll();

        if ($type) {
            $rows = $rows->where('type', $type)->values();
        }

        $counts = [
            'live' => $rows->where('lifecycle', 'live')->count(),
            'expiring' => $rows->where('lifecycle', 'expiring')->count(),
            'expired' => $rows->where('lifecycle', 'expired')->count(),
            'pending' => $rows->where('lifecycle', 'pending')->count(),
            'total' => $rows->count(),
        ];

        if ($bucket && isset($counts[$bucket])) {
            $rows = $rows->where('lifecycle', $bucket)->values();
        }

        return [
            'counts' => $counts,
            'rows' => $rows,
            'expiring_days' => self::EXPIRING_DAYS,
            'types' => $rows->pluck('type')->unique()->sort()->values()->all(),
        ];
    }

    public function collectAll(): Collection
    {
        $now = now();
        $expiringUntil = $now->copy()->addDays(self::EXPIRING_DAYS);

        return collect()
            ->merge($this->mapFeatured($now, $expiringUntil))
            ->merge($this->mapSponsored($now, $expiringUntil))
            ->merge($this->mapPromoted($now, $expiringUntil))
            ->merge($this->mapBanners($now, $expiringUntil))
            ->merge($this->mapBuySell($now, $expiringUntil))
            ->merge($this->mapVehicles($now, $expiringUntil))
            ->sortBy(fn ($row) => $row['expires_at']?->timestamp ?? PHP_INT_MAX)
            ->values();
    }

    protected function mapFeatured(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return FeaturedAdvert::query()
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(function (FeaturedAdvert $ad) use ($now, $expiringUntil) {
                    $expires = $ad->expires_at;
                    $pending = $this->isPendingPayment($ad->payment_status);
                    $active = (bool) $ad->is_active && ! $pending;

                    return $this->row(
                        type: 'Featured',
                        id: (string) $ad->id,
                        title: $ad->title ?: 'Untitled featured',
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active,
                        statusLabel: $ad->payment_status ?: ($active ? 'active' : 'inactive'),
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => FeaturedAdvertResource::getUrl('edit', ['record' => $ad])),
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function mapSponsored(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return SponsoredAdvert::query()
                ->latest('sponsored_advert_id')
                ->limit(500)
                ->get()
                ->map(function (SponsoredAdvert $ad) use ($now, $expiringUntil) {
                    $expires = $ad->sponsorship_end_date;
                    $pending = $this->isPendingPayment($ad->payment_status ?? null);
                    $active = (bool) $ad->is_active && ! $pending;

                    return $this->row(
                        type: 'Sponsored',
                        id: (string) ($ad->sponsored_advert_id ?? $ad->id),
                        title: $ad->title ?: 'Untitled sponsored',
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active,
                        statusLabel: $ad->status ?? ($ad->payment_status ?: 'unknown'),
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => SponsoredAdvertResource::getUrl('edit', ['record' => $ad])),
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function mapPromoted(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return PromotedAdvert::query()
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(function (PromotedAdvert $ad) use ($now, $expiringUntil) {
                    $expires = $ad->promotion_end ? Carbon::parse($ad->promotion_end) : null;
                    $status = strtolower((string) ($ad->status ?? ''));
                    $pending = in_array($status, ['pending', 'pending_payment'], true)
                        || $this->isPendingPayment($ad->payment_status ?? null);
                    $active = (bool) $ad->is_active && $status !== 'expired' && ! $pending;

                    return $this->row(
                        type: 'Promoted',
                        id: (string) $ad->id,
                        title: $ad->title ?: 'Untitled promoted',
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active,
                        statusLabel: $ad->status ?: ($active ? 'active' : 'inactive'),
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => PromotedAdvertResource::getUrl('edit', ['record' => $ad])),
                        forceExpired: $status === 'expired',
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function mapBanners(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return BannerAd::query()
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(function (BannerAd $ad) use ($now, $expiringUntil) {
                    $expires = $ad->promotion_end
                        ? Carbon::parse($ad->promotion_end)
                        : ($ad->validity_end ? Carbon::parse($ad->validity_end) : null);
                    $status = strtolower((string) ($ad->status ?? ''));
                    $pending = in_array($status, ['pending', 'pending_payment'], true)
                        || $this->isPendingPayment($ad->payment_status ?? null);
                    $active = (bool) $ad->is_active && $status !== 'expired' && ! $pending;

                    return $this->row(
                        type: 'Banner',
                        id: (string) $ad->id,
                        title: $ad->title ?: 'Untitled banner',
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active,
                        statusLabel: $ad->status ?: ($active ? 'active' : 'inactive'),
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => BannerAdResource::getUrl('edit', ['record' => $ad])),
                        forceExpired: $status === 'expired',
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function mapBuySell(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return BuySellAdvert::query()
                ->latest('created_at')
                ->limit(500)
                ->get()
                ->map(function (BuySellAdvert $ad) use ($now, $expiringUntil) {
                    $expires = $ad->expires_at ?: $ad->promotion_end_date;
                    $status = strtolower((string) ($ad->status ?? ''));
                    $pay = strtolower((string) ($ad->payment_status ?? $ad->promotion_status ?? ''));
                    $pending = in_array($status, ['pending', 'pending_payment'], true)
                        || $this->isPendingPayment($pay);
                    $active = in_array($status, ['active', 'approved', 'published'], true) && ! $pending;

                    return $this->row(
                        type: 'Buy & Sell',
                        id: (string) $ad->id,
                        title: $ad->title ?: 'Untitled listing',
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active,
                        statusLabel: $ad->status ?: 'unknown',
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => BuySellAdvertResource::getUrl('edit', ['record' => $ad])),
                        forceExpired: $status === 'expired',
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function mapVehicles(Carbon $now, Carbon $expiringUntil): Collection
    {
        try {
            return Vehicle::query()
                ->latest('id')
                ->limit(500)
                ->get()
                ->map(function (Vehicle $ad) use ($now, $expiringUntil) {
                    $expires = $ad->expires_at;
                    $status = strtolower((string) ($ad->status ?? ''));
                    $pending = in_array($status, ['pending', 'pending_payment'], true)
                        || $this->isPendingPayment($ad->payment_status ?? null);
                    $active = in_array($status, ['active', 'approved', 'published'], true)
                        || ((bool) ($ad->is_active ?? false) && ! $pending);

                    $title = trim(implode(' ', array_filter([
                        $ad->year ?? null,
                        $ad->make ?? $ad->vehicle_make ?? null,
                        $ad->model ?? $ad->vehicle_model ?? null,
                        $ad->title ?? null,
                    ]))) ?: 'Untitled vehicle';

                    return $this->row(
                        type: 'Vehicles',
                        id: (string) $ad->id,
                        title: $title,
                        expiresAt: $expires,
                        pending: $pending,
                        active: $active && $status !== 'expired',
                        statusLabel: $ad->status ?: ($active ? 'active' : 'inactive'),
                        now: $now,
                        expiringUntil: $expiringUntil,
                        editUrl: $this->safeUrl(fn () => VehicleResource::getUrl('edit', ['record' => $ad])),
                        forceExpired: $status === 'expired',
                    );
                });
        } catch (Throwable) {
            return collect();
        }
    }

    protected function row(
        string $type,
        string $id,
        string $title,
        mixed $expiresAt,
        bool $pending,
        bool $active,
        string $statusLabel,
        Carbon $now,
        Carbon $expiringUntil,
        ?string $editUrl,
        bool $forceExpired = false,
    ): array {
        $expires = $expiresAt instanceof Carbon
            ? $expiresAt
            : ($expiresAt ? Carbon::parse($expiresAt) : null);

        $lifecycle = $this->classify($pending, $active, $expires, $now, $expiringUntil, $forceExpired);

        return [
            'type' => $type,
            'id' => $id,
            'title' => $title,
            'status' => $statusLabel,
            'lifecycle' => $lifecycle,
            'expires_at' => $expires,
            'expires_label' => $expires ? $expires->timezone(config('app.timezone'))->format('Y-m-d H:i') : '—',
            'days_left' => $expires
                ? (int) floor(($expires->getTimestamp() - $now->getTimestamp()) / 86400)
                : null,
            'edit_url' => $editUrl,
        ];
    }

    protected function classify(
        bool $pending,
        bool $active,
        ?Carbon $expires,
        Carbon $now,
        Carbon $expiringUntil,
        bool $forceExpired,
    ): string {
        if ($pending) {
            return 'pending';
        }

        if ($forceExpired || ($expires && $expires->lt($now))) {
            return 'expired';
        }

        if ($expires && $expires->lte($expiringUntil) && $active) {
            return 'expiring';
        }

        if ($active) {
            return 'live';
        }

        // Inactive but not yet past expiry → treat as expired/off for admin clarity
        return $expires && $expires->gte($now) ? 'expired' : 'expired';
    }

    protected function isPendingPayment(?string $paymentStatus): bool
    {
        $pay = strtolower((string) $paymentStatus);

        return in_array($pay, ['pending', 'pending_payment', 'unpaid'], true);
    }

    protected function safeUrl(callable $cb): ?string
    {
        try {
            return $cb();
        } catch (Throwable) {
            return null;
        }
    }
}
