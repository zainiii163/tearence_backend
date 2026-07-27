<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach ([
                ['Total', $stats['total'] ?? 0],
                ['Active', $stats['active'] ?? 0],
                ['Premium', $stats['premium'] ?? 0],
                ['Catalog', $stats['catalog'] ?? 0],
                ['Seller listings', $stats['seller'] ?? 0],
                ['Purchases', $stats['purchases'] ?? 0],
            ] as [$label, $value])
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 p-4 dark:bg-gray-900 dark:ring-white/10">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 p-5 dark:bg-amber-950/30 dark:ring-amber-500/30">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Premium monthly fee</p>
                <p class="mt-1 text-3xl font-bold text-amber-950 dark:text-white">
                    ${{ number_format((float) $premium_monthly_fee, 2) }}
                    <span class="text-sm font-medium text-amber-800 dark:text-amber-300">/ {{ $premium_duration_days }} days</span>
                </p>
                <p class="mt-2 text-xs text-amber-800 dark:text-amber-300">
                    Editable by super admin — not hard-coded. Change it under Template Pricing.
                </p>
            </div>
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 p-5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Platform fee from template sales</p>
                <p class="mt-1 text-3xl font-bold text-gray-950 dark:text-white">
                    ${{ number_format((float) ($stats['revenue'] ?? 0), 2) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 overflow-hidden dark:bg-gray-900 dark:ring-white/10">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-950 dark:text-white">Recent templates</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($recent as $item)
                        <div class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $item->title }}</p>
                                <p class="text-xs text-gray-500">{{ $item->vertical }} · {{ $item->status }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold">${{ number_format((float) $item->price, 2) }}</p>
                                @if ($item->is_premium_active)
                                    <span class="text-[10px] font-bold uppercase text-amber-600">Premium</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-sm text-gray-500 text-center">No templates yet. Run migrate + BusinessTemplateSeeder.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 overflow-hidden dark:bg-gray-900 dark:ring-white/10">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-950 dark:text-white">Recent purchases</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($recent_purchases as $purchase)
                        <div class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $purchase->title }}</p>
                                <p class="text-xs text-gray-500">{{ $purchase->payment_status }} · {{ $purchase->created_at?->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm font-semibold shrink-0">${{ number_format((float) $purchase->price_paid, 2) }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-sm text-gray-500 text-center">No purchases yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
