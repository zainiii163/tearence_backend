@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('jobs.browse') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Jobs
        </a>
    </div>

    <!-- Job Header -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $job->title }}</h1>
                    <div class="flex items-center space-x-4 text-gray-600">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $job->company_name }}
                        </span>
                        @if($job->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $job->category->name }}
                            </span>
                        @endif
                        @if($job->promotion_type != 'basic')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
                                $job->promotion_type == 'featured' ? 'bg-yellow-100 text-yellow-800' : 
                                ($job->promotion_type == 'sponsored' ? 'bg-green-100 text-green-800' : 
                                ($job->promotion_type == 'network' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ ucfirst($job->promotion_type) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if($job->logo_url)
                    <img src="{{ $job->logo_url }}" alt="{{ $job->company_name }}" class="w-20 h-20 rounded-lg ml-4">
                @endif
            </div>
        </div>

        <!-- Job Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Job Description</h2>
                    <div class="prose prose-gray max-w-none">
                        <p>{{ $job->description }}</p>
                    </div>
                </div>

                <!-- Responsibilities -->
                @if($job->responsibilities)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Responsibilities</h2>
                        <div class="prose prose-gray max-w-none">
                            <p>{{ $job->responsibilities }}</p>
                        </div>
                    </div>
                @endif

                <!-- Requirements -->
                @if($job->requirements)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirements</h2>
                        <div class="prose prose-gray max-w-none">
                            <p>{{ $job->requirements }}</p>
                        </div>
                    </div>
                @endif

                <!-- Benefits -->
                @if($job->benefits)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Benefits</h2>
                        <div class="prose prose-gray max-w-none">
                            <p>{{ $job->benefits }}</p>
                        </div>
                    </div>
                @endif

                <!-- Skills -->
                @if($job->skills_needed)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Skills Required</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach(explode(',', $job->skills_needed) as $skill)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    {{ trim($skill) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Company Information -->
                @if($job->company_description || $job->company_website || $job->company_social)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">About {{ $job->company_name }}</h2>
                        @if($job->company_description)
                            <div class="prose prose-gray max-w-none mb-4">
                                <p>{{ $job->company_description }}</p>
                            </div>
                        @endif
                        @if($job->company_website || $job->company_social)
                            <div class="space-y-2">
                                @if($job->company_website)
                                    <a href="{{ $job->company_website }}" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        {{ $job->company_website }}
                                    </a>
                                @endif
                                @if($job->company_social && is_array($job->company_social))
                                    @foreach($job->company_social as $platform => $url)
                                        <a href="{{ $url }}" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            {{ ucfirst($platform) }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Job Info Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>{{ $job->city }}, {{ $job->country }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
                            </svg>
                            <span>{{ $job->work_type }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2zm0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1l8 8m-8-8l-8 8"></path>
                            </svg>
                            <span>{{ $job->formatted_salary ?: 'Negotiable' }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.707.293H19a2 2 0 002-2v-4a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v4z"></path>
                            </svg>
                            <span>{{ $job->experience_level_label }}</span>
                        </div>
                        
                        @if($job->remote_available)
                            <div class="flex items-center text-sm">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v1a2 2 0 002-2h1.055M5 19h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-green-600">Remote Available</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Application Method -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">How to Apply</h3>
                    <div class="space-y-3">
                        @if($job->application_method == 'email')
                            <div class="text-sm">
                                <p class="font-medium text-gray-700">Email:</p>
                                <a href="mailto:{{ $job->application_email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $job->application_email }}
                                </a>
                            </div>
                        @endif
                        
                        @if($job->application_method == 'website')
                            <div class="text-sm">
                                <p class="font-medium text-gray-700">Apply via Website:</p>
                                <a href="{{ $job->application_website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $job->application_website }}
                                </a>
                            </div>
                        @endif
                        
                        @if($job->application_method == 'phone')
                            <div class="text-sm">
                                <p class="font-medium text-gray-700">Phone:</p>
                                <span class="text-gray-900">{{ $job->application_phone }}</span>
                            </div>
                        @endif
                        
                        @if($job->application_instructions)
                            <div class="text-sm">
                                <p class="font-medium text-gray-700 mb-1">Instructions:</p>
                                <p class="text-gray-600">{{ $job->application_instructions }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Views</span>
                            <span class="font-medium">{{ $job->views }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Applications</span>
                            <span class="font-medium">{{ $job->applications_count }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Posted</span>
                            <span class="font-medium">{{ $job->posted_at->format('M j, Y') }}</span>
                        </div>
                        @if($job->expires_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Expires</span>
                                <span class="font-medium">{{ $job->expires_at->format('M j, Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                @if(Auth::check())
                    <div class="space-y-3">
                        @if(!$hasApplied)
                            <button onclick="showApplicationModal()" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                                Apply Now
                            </button>
                        @else
                            <div class="w-full bg-green-100 text-green-800 px-4 py-3 rounded-lg text-center font-medium">
                                ✓ Application Sent
                            </div>
                        @endif
                        
                        <button onclick="toggleSaveJob()" id="saveJobBtn" class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition font-medium flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span id="saveJobText">{{ $isSaved ? 'Saved' : 'Save Job' }}</span>
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        <a href="{{ route('login') }}" class="block w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                            Login to Apply
                        </a>
                        <a href="{{ route('register') }}" class="block w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition font-medium text-center">
                            Create Account
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Similar Jobs -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Similar Jobs</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Similar jobs would be loaded here -->
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
                </svg>
                <p>Loading similar jobs...</p>
            </div>
        </div>
    </div>
</div>

<!-- Application Modal -->
@if(Auth::check() && !$hasApplied)
<div id="applicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Apply for {{ $job->title }}</h3>
                <button onclick="closeApplicationModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="applicationForm" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="full_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary</label>
                        <input type="text" name="expected_salary" placeholder="e.g., 80000-100000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter</label>
                    <textarea name="cover_letter" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tell us why you're interested in this position..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio Links</label>
                    <input type="url" name="portfolio_links[]" placeholder="https://yourportfolio.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2">
                    <input type="url" name="portfolio_links[]" placeholder="https://github.com/username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApplicationModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function showApplicationModal() {
    document.getElementById('applicationModal').classList.remove('hidden');
}

function closeApplicationModal() {
    document.getElementById('applicationModal').classList.add('hidden');
}

function toggleSaveJob() {
    const button = document.getElementById('saveJobBtn');
    const saveText = document.getElementById('saveJobText');
    const originalText = saveText.textContent;
    
    button.disabled = true;
    saveText.textContent = 'Loading...';
    
    fetch(`/api/v1/jobs/{{ $job->id }}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveText.textContent = data.saved ? 'Saved' : 'Save Job';
            button.classList.toggle('text-blue-600', !data.saved);
            button.classList.toggle('text-gray-700', data.saved);
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

document.getElementById('applicationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Handle portfolio_links array
    const portfolioLinks = formData.getAll('portfolio_links');
    data.portfolio_links = portfolioLinks.filter(link => link.trim() !== '');
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
    
    fetch(`/api/v1/jobs/{{ $job->id }}/apply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeApplicationModal();
            location.reload();
        } else {
            alert('Error submitting application: ' + (data.error?.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error submitting application: ' + error.message);
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = 'Submit Application';
    });
});
</script>
@endsection
