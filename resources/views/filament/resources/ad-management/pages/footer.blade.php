<!-- Responsive Footer with Quick Actions -->
<div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Quick Actions</h4>
            <div class="space-y-2">
                <button wire:click="$wire.mount('edit')" 
                    class="w-full text-left px-3 py-2 text-sm bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors">
                    Edit Ad
                </button>
                <button wire:click="toggle_status" 
                    class="w-full text-left px-3 py-2 text-sm bg-yellow-50 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 rounded hover:bg-yellow-100 dark:hover:bg-yellow-800 transition-colors">
                    {{ $record->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>

        <!-- Ad Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Ad Details</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Type:</span>
                    <span class="text-gray-900 dark:text-white">{{ ucfirst($record->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="text-gray-900 dark:text-white">{{ $record->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Payment:</span>
                    <span class="text-gray-900 dark:text-white">{{ ucfirst($record->payment_status) }}</span>
                </div>
            </div>
        </div>

        <!-- Schedule -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Schedule</h4>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Start:</span>
                    <span class="text-gray-900 dark:text-white block">{{ $record->start_date->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">End:</span>
                    <span class="text-gray-900 dark:text-white block">{{ $record->end_date->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Duration:</span>
                    <span class="text-gray-900 dark:text-white block">{{ $record->start_date->diffInDays($record->end_date) }} days</span>
                </div>
            </div>
        </div>

        <!-- Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Performance</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Price:</span>
                    <span class="text-gray-900 dark:text-white">${{ number_format($record->price, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Days Active:</span>
                    <span class="text-gray-900 dark:text-white">{{ $record->is_active ? $record->start_date->diffInDays(now()) : 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Days Left:</span>
                    <span class="text-gray-900 dark:text-white">{{ max(0, $record->end_date->diffInDays(now())) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
