<x-filament-panels::page>
    @php
        $bucketMeta = [
            'live' => ['label' => 'Live', 'color' => 'bg-emerald-500', 'ring' => 'ring-emerald-500'],
            'expiring' => ['label' => 'Expiring', 'color' => 'bg-amber-500', 'ring' => 'ring-amber-500'],
            'expired' => ['label' => 'Expired', 'color' => 'bg-rose-500', 'ring' => 'ring-rose-500'],
            'pending' => ['label' => 'Pending payment', 'color' => 'bg-sky-500', 'ring' => 'ring-sky-500'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($bucketMeta as $key => $meta)
                <button
                    type="button"
                    wire:click="setBucket('{{ $key }}')"
                    class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 text-left shadow-sm transition hover:shadow-md {{ $bucket === $key ? 'ring-2 '.$meta['ring'] : '' }}"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ $meta['label'] }}
                            </p>
                            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $counts[$key] ?? 0 }}
                            </p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full {{ $meta['color'] }} text-white text-sm font-bold">
                            {{ strtoupper(substr($meta['label'], 0, 1)) }}
                        </span>
                    </div>
                    @if ($key === 'expiring')
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Within next {{ $expiringDays }} days
                        </p>
                    @endif
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="typeFilter">
                Type filter
            </label>
            <select
                id="typeFilter"
                wire:model.live="typeFilter"
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm"
            >
                <option value="all">All types</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing <strong>{{ $rows->count() }}</strong> in
                <strong>{{ $bucketMeta[$bucket]['label'] ?? $bucket }}</strong>
                · total tracked {{ $counts['total'] ?? 0 }}
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Expires</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Days left</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Open</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                    {{ $row['type'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-100 max-w-md">
                                    <div class="truncate" title="{{ $row['title'] }}">{{ $row['title'] }}</div>
                                    <div class="text-xs text-gray-400">#{{ $row['id'] }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        @if ($row['lifecycle'] === 'live') bg-emerald-100 text-emerald-800
                                        @elseif ($row['lifecycle'] === 'expiring') bg-amber-100 text-amber-800
                                        @elseif ($row['lifecycle'] === 'pending') bg-sky-100 text-sky-800
                                        @else bg-rose-100 text-rose-800
                                        @endif
                                    ">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                    {{ $row['expires_label'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-200">
                                    @if ($row['days_left'] === null)
                                        —
                                    @elseif ($row['days_left'] < 0)
                                        <span class="text-rose-600 font-semibold">{{ abs($row['days_left']) }}d ago</span>
                                    @else
                                        {{ $row['days_left'] }}d
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if (!empty($row['edit_url']))
                                        <a href="{{ $row['edit_url'] }}" class="text-primary-600 hover:underline font-semibold">
                                            Edit
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No adverts in this bucket
                                    @if ($typeFilter !== 'all')
                                        for type “{{ $typeFilter }}”
                                    @endif
                                    .
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
