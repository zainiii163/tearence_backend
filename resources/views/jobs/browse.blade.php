@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Browse Jobs</h1>
        <p class="text-gray-600 mt-2">Find your next opportunity</p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form method="GET" action="{{ route('jobs.browse') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Keywords, job title, company..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" name="location" value="{{ request('location') }}" placeholder="City, Country..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Work Type</label>
                    <select name="work_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="Full-time" {{ request('work_type') == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Part-time" {{ request('work_type') == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="Contract" {{ request('work_type') == 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Freelance" {{ request('work_type') == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="Internship" {{ request('work_type') == 'Internship' ? 'selected' : '' }}>Internship</option>
                        <option value="Temporary" {{ request('work_type') == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Experience Level</label>
                    <select name="experience_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Levels</option>
                        <option value="entry" {{ request('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level</option>
                        <option value="mid" {{ request('experience_level') == 'mid' ? 'selected' : '' }}>Mid Level</option>
                        <option value="senior" {{ request('experience_level') == 'senior' ? 'selected' : '' }}>Senior Level</option>
                        <option value="executive" {{ request('experience_level') == 'executive' ? 'selected' : '' }}>Executive</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" name="remote_only" value="1" {{ request('remote_only') ? 'checked' : '' }} 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Remote Only</span>
                    </label>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Search Jobs
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">
            Showing {{ $jobs->count() }} of {{ $jobs->total() }} jobs
        </p>
        <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700">Sort by:</label>
            <select onchange="window.location.href='{{ request()->fullUrlWithQuery(['sort_by' => this.value]) }}" 
                    class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="most_recent" {{ request('sort_by') == 'most_recent' ? 'selected' : '' }}>Most Recent</option>
                <option value="salary_high_low" {{ request('sort_by') == 'salary_high_low' ? 'selected' : '' }}>Salary: High to Low</option>
                <option value="salary_low_high" {{ request('sort_by') == 'salary_low_high' ? 'selected' : '' }}>Salary: Low to High</option>
                <option value="most_viewed" {{ request('sort_by') == 'most_viewed' ? 'selected' : '' }}>Most Viewed</option>
            </select>
        </div>
    </div>

    <!-- Jobs Grid -->
    @if($jobs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($jobs as $job)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                    <!-- Job Header -->
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    <a href="{{ route('jobs.show', $job->slug) }}" class="hover:text-blue-600 transition">
                                        {{ $job->title }}
                                    </a>
                                </h3>
                                <p class="text-gray-600">{{ $job->company_name }}</p>
                            </div>
                            @if($job->logo_url)
                                <img src="{{ $job->logo_url }}" alt="{{ $job->company_name }}" class="w-12 h-12 rounded-lg ml-4">
                            @endif
                        </div>

                        <!-- Job Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $job->city }}, {{ $job->country }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
                                </svg>
                                {{ $job->work_type }}
                            </div>
                            
                            @if($job->salary_range)
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2zm0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1l8 8m-8-8l-8 8"></path>
                                    </svg>
                                    {{ $job->formatted_salary }}
                                </div>
                            @endif
                            
                            @if($job->remote_available)
                                <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v1a2 2 0 002-2h1.055M5 19h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Remote Available
                                </div>
                            @endif
                        </div>

                        <!-- Promotion Badge -->
                        @if($job->promotion_type != 'basic')
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                    $job->promotion_type == 'featured' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($job->promotion_type == 'sponsored' ? 'bg-green-100 text-green-800' : 
                                    ($job->promotion_type == 'network' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ ucfirst($job->promotion_type) }}
                                </span>
                            </div>
                        @endif

                        <!-- Description Preview -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {{ Str::limit(strip_tags($job->description), 150) }}
                        </p>

                        <!-- Skills -->
                        @if($job->skills_needed)
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice(explode(',', $job->skills_needed), 0, 3) as $skill)
                                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                            {{ trim($skill) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Stats -->
                        <div class="flex items-center justify-between text-sm text-gray-500 pt-4 border-t border-gray-100">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ $job->views }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                    {{ $job->applications_count }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ $job->posted_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('jobs.show', $job->slug) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                View Details →
                            </a>
                            @if(Auth::check())
                                <button onclick="toggleSaveJob({{ $job->id }}, this)" 
                                        class="text-gray-600 hover:text-blue-600 font-medium text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span class="save-text-{{ $job->id }}">Save</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $jobs->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
            <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria</p>
            <div class="mt-6">
                <a href="{{ route('jobs.browse') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Clear Filters
                </a>
            </div>
        </div>
    @endif
</div>

<script>
function toggleSaveJob(jobId, button) {
    const saveText = button.querySelector('.save-text-' + jobId);
    const originalText = saveText.textContent;
    
    button.disabled = true;
    saveText.textContent = 'Loading...';
    
    fetch(`/api/v1/jobs/${jobId}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveText.textContent = data.saved ? 'Saved' : 'Save';
            button.classList.toggle('text-blue-600', data.saved);
            button.classList.toggle('text-gray-600', !data.saved);
        } else {
            saveText.textContent = originalText;
        }
    })
    .catch(error => {
        saveText.textContent = originalText;
    })
    .finally(() => {
        button.disabled = false;
    });
}
</script>
@endsection
