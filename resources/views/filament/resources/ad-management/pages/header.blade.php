<!-- Responsive Header Stats -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Total Ads</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $stats['total_ads'] }}</p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900 p-2 rounded-lg">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Active</p>
                <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $stats['active_ads'] }}</p>
            </div>
            <div class="bg-green-100 dark:bg-green-900 p-2 rounded-lg">
                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400">Expired</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $stats['expired_ads'] }}</p>
            </div>
            <div class="bg-red-100 dark:bg-red-900 p-2 rounded-lg">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 lg:col-span-1 sm:col-span-2">
        <div class="flex items-center justify-between">
            <div class="w-full">
                <p class="text-xs text-gray-600 dark:text-gray-400">Performance</p>
                <div class="flex items-center">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['total_ads'] > 0 ? ($stats['active_ads'] / $stats['total_ads']) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-900 dark:text-white">
                        {{ $stats['total_ads'] > 0 ? round(($stats['active_ads'] / $stats['total_ads']) * 100, 0) : 0 }}%
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
