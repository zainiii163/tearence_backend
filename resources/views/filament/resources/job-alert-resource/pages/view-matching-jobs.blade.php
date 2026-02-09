<x-filament-panels::page>
    <div class="mb-4">
        <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">Alert Details</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-500">Alert Name:</span>
                    <p class="font-medium">{{ $this->record->name }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Customer:</span>
                    <p class="font-medium">{{ $this->record->customer->first_name }} {{ $this->record->customer->last_name }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Keywords:</span>
                    <p class="font-medium">{{ implode(', ', $this->record->keywords ?? []) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Location:</span>
                    <p class="font-medium">{{ $this->record->location->city ?? 'Any' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Job Types:</span>
                    <p class="font-medium">{{ implode(', ', $this->record->job_type ?? []) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Frequency:</span>
                    <p class="font-medium">{{ ucfirst($this->record->frequency) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>

