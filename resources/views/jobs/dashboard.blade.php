@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Jobs Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage your job postings and applications</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Jobs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_jobs'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Active Jobs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_jobs'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Applications Received</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_applications_received'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 7.974 6 10v4.158c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Applications Sent</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_applications_sent'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Views</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_views'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-pink-100 rounded-lg p-3">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Saved Jobs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['saved_jobs'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('jobs.create') }}" class="bg-blue-600 text-white rounded-lg p-6 hover:bg-blue-700 transition">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <div>
                    <p class="font-semibold">Post New Job</p>
                    <p class="text-sm opacity-90">Create a new job posting</p>
                </div>
            </div>
        </a>

        <a href="{{ route('jobs.my-applications') }}" class="bg-purple-600 text-white rounded-lg p-6 hover:bg-purple-700 transition">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <div>
                    <p class="font-semibold">My Applications</p>
                    <p class="text-sm opacity-90">View sent applications</p>
                </div>
            </div>
        </a>

        <a href="{{ route('jobs.seeker.profile') }}" class="bg-green-600 text-white rounded-lg p-6 hover:bg-green-700 transition">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <div>
                    <p class="font-semibold">Seeker Profile</p>
                    <p class="text-sm opacity-90">Manage your profile</p>
                </div>
            </div>
        </a>

        <a href="{{ route('jobs.browse') }}" class="bg-orange-600 text-white rounded-lg p-6 hover:bg-orange-700 transition">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div>
                    <p class="font-semibold">Browse Jobs</p>
                    <p class="text-sm opacity-90">Find opportunities</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Jobs Posted -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Jobs Posted</h2>
            </div>
            <div class="p-6">
                @if($recentJobs->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentJobs as $job)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.002 9.002 0 1112 21v-7.745l9-9"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $job->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $job->company_name }} • {{ $job->work_type }}</p>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $job->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $job->applications_count }} applications</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('jobs.edit', $job->id) }}" class="text-blue-600 hover:text-blue-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No jobs posted yet</p>
                @endif
            </div>
        </div>

        <!-- Recent Applications Sent -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Applications Sent</h2>
            </div>
            <div class="p-6">
                @if($recentApplications->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentApplications as $application)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $application->job->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $application->job->company_name }}</p>
                                    <div class="flex items-center mt-1 space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $application->status == 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $application->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No applications sent yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Job Seeker Profile & Alerts -->
    @if($jobSeekerProfile || $jobAlerts->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Job Seeker Profile -->
            @if($jobSeekerProfile)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Job Seeker Profile</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            @if($jobSeekerProfile->profile_photo_url)
                                <img src="{{ $jobSeekerProfile->profile_photo_url }}" alt="{{ $jobSeekerProfile->full_name }}" class="w-16 h-16 rounded-full">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $jobSeekerProfile->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $jobSeekerProfile->profession }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $jobSeekerProfile->views }}</p>
                                <p class="text-sm text-gray-500">Profile Views</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $jobSeekerProfile->contact_count }}</p>
                                <p class="text-sm text-gray-500">Contacts</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $jobSeekerProfile->applications()->count() }}</p>
                                <p class="text-sm text-gray-500">Applications</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('jobs.seeker.profile') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Edit Profile →
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Job Alerts -->
            @if($jobAlerts->count() > 0)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Job Alerts</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($jobAlerts as $alert)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $alert->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $alert->frequency }} • {{ $alert->jobs_sent_count }} jobs sent</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $alert->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $alert->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('jobs.alerts') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Manage Alerts →
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Active Upsells -->
    @if($activeUpsells->count() > 0)
        <div class="bg-white rounded-lg shadow mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Active Promotions</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($activeUpsells as $upsell)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $upsell->upsell_type_label }}</p>
                                <p class="text-sm text-gray-500">{{ $upsell->upsellable_type }} • {{ $upsell->formatted_price }}</p>
                                <p class="text-xs text-gray-500">Expires: {{ $upsell->expires_at ? $upsell->expires_at->format('M j, Y') : 'Never' }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
