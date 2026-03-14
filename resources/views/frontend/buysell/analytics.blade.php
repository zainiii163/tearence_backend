@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Advert Analytics</h1>
            <p class="text-gray-600">{{ $advert->title }}</p>
        </div>
        <a href="{{ route('buysell.my-adverts') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to My Adverts
        </a>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Views</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($advert->views_count ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Favorites</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($advert->favorites_count ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Enquiries</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($advert->enquiries_count ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Shares</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($advert->shares_count ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Views Chart -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Views Over Last 30 Days</h2>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <canvas id="viewsChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Device Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Device Breakdown</h2>
            <div class="space-y-4">
                @foreach($deviceBreakdown as $device => $percentage)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ ucfirst($device) }}</span>
                            <span class="text-sm text-gray-500">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Locations</h2>
            @if($locationBreakdown->count() > 0)
                <div class="space-y-3">
                    @foreach($locationBreakdown as $location)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">{{ $location->country }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $location->count }} views</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No location data available</p>
            @endif
        </div>
    </div>

    <!-- Advert Details -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Advert Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600">Title</p>
                <p class="font-medium text-gray-900">{{ $advert->title }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Price</p>
                <p class="font-medium text-gray-900">${{ number_format($advert->price, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Category</p>
                <p class="font-medium text-gray-900">{{ $advert->category->icon }} {{ $advert->category->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Condition</p>
                <p class="font-medium text-gray-900">{{ ucfirst($advert->condition) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p class="font-medium text-gray-900">{{ ucfirst($advert->status) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Created</p>
                <p class="font-medium text-gray-900">{{ $advert->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
// Simple chart implementation (in production, use Chart.js or similar)
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('viewsChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const viewsData = @json($dailyViews->map(function($item) { return ['date' => $item->date, 'views' => $item->views]; }));
        
        // Simple bar chart
        const maxValue = Math.max(...viewsData.map(d => d.views));
        const chartHeight = canvas.height - 40;
        const chartWidth = canvas.width - 40;
        const barWidth = chartWidth / viewsData.length;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw bars
        viewsData.forEach((data, index) => {
            const barHeight = (data.views / maxValue) * chartHeight;
            const x = index * barWidth + 20;
            const y = chartHeight - barHeight + 20;
            
            ctx.fillStyle = '#3B82F6';
            ctx.fillRect(x, y, barWidth - 10, barHeight);
            
            // Draw label
            ctx.fillStyle = '#666';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(data.views, x + (barWidth - 10) / 2, y - 5);
        });
    }
});
</script>
@endsection
