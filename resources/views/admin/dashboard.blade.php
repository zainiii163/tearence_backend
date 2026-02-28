@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Admin Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-xl font-semibold text-gray-900">Admin Dashboard</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="live-toggle-btn" onclick="toggleLiveUpdates()" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 text-sm">
                        <i class="fas fa-pause mr-2"></i>Pause Live Updates
                    </button>
                    <span class="text-sm text-gray-600">Welcome, Admin</span>
                    <button onclick="window.location.href='/dashboard'" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-arrow-left mr-1"></i> Back to User Dashboard
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 overflow-x-auto">
                <button onclick="showSection('overview')" class="admin-nav-item px-3 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600 whitespace-nowrap" data-section="overview">
                    <i class="fas fa-chart-line mr-2"></i>Overview
                </button>
                <button onclick="showSection('ads')" class="admin-nav-item px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent whitespace-nowrap" data-section="ads">
                    <i class="fas fa-ad mr-2"></i>Ad Management
                </button>
                <button onclick="showSection('listings')" class="admin-nav-item px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent whitespace-nowrap" data-section="listings">
                    <i class="fas fa-list mr-2"></i>Listings
                </button>
                <button onclick="showSection('users')" class="admin-nav-item px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent whitespace-nowrap" data-section="users">
                    <i class="fas fa-users mr-2"></i>Users
                </button>
                <button onclick="showSection('upsells')" class="admin-nav-item px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent whitespace-nowrap" data-section="upsells">
                    <i class="fas fa-star mr-2"></i>Upsells
                </button>
                <button onclick="showSection('reports')" class="admin-nav-item px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent whitespace-nowrap" data-section="reports">
                    <i class="fas fa-chart-bar mr-2"></i>Reports
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Overview Section -->
        <section id="overview-section" class="admin-section">
            <!-- Revenue Breakdown Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-revenue">-</p>
                            <p class="text-xs text-gray-500">All time</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-dollar-sign text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">This Month</p>
                            <p class="text-2xl font-bold text-gray-900" id="this-month-revenue">-</p>
                            <p class="text-xs text-gray-500" id="current-month-label">February 2020</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-calendar-alt text-green-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">This Week</p>
                            <p class="text-2xl font-bold text-gray-900" id="this-week-revenue">-</p>
                            <p class="text-xs text-gray-500">Last 7 days</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-calendar-week text-blue-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Today</p>
                            <p class="text-2xl font-bold text-gray-900" id="today-revenue">-</p>
                            <p class="text-xs text-gray-500">Today's earnings</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-calendar-day text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ad Revenue Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Ad Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-ad-revenue">-</p>
                            <p class="text-xs text-gray-500">Banner + Affiliate ads</p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-3">
                            <i class="fas fa-ad text-orange-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Banner Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="banner-revenue">-</p>
                            <p class="text-xs text-gray-500">Revenue from banner advertisements</p>
                        </div>
                        <div class="bg-indigo-100 rounded-full p-3">
                            <i class="fas fa-image text-indigo-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Affiliate Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="affiliate-revenue">-</p>
                            <p class="text-xs text-gray-500">Revenue from affiliate advertisements</p>
                        </div>
                        <div class="bg-teal-100 rounded-full p-3">
                            <i class="fas fa-link text-teal-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Listings</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-listings">-</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-list text-blue-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Ads</p>
                            <p class="text-2xl font-bold text-gray-900" id="active-ads">-</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-ad text-green-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-users">-</p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Other Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="other-revenue">-</p>
                            <p class="text-xs text-gray-500">Upsells, listings, etc.</p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3">
                            <i class="fas fa-coins text-gray-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div id="recent-activity" class="space-y-4">
                        <!-- Activity items will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Ad Management Section -->
        <section id="ads-section" class="admin-section hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Ad Management</h2>
                    <button onclick="createNewAd()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        <i class="fas fa-plus mr-2"></i>Create New Ad
                    </button>
                </div>
                <div class="p-6">
                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <input type="text" id="ad-search" placeholder="Search ads..." class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select id="ad-status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Expired</option>
                        </select>
                        <select id="ad-type-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Types</option>
                            <option value="banner">Banner</option>
                            <option value="sponsored">Sponsored</option>
                            <option value="featured">Featured</option>
                        </select>
                        <button onclick="filterAds()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>

                    <!-- Ads Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ads-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- Ads will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="ads-pagination" class="mt-6 flex justify-center">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Listings Section -->
        <section id="listings-section" class="admin-section hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Listings Management</h2>
                </div>
                <div class="p-6">
                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <input type="text" id="listing-search" placeholder="Search listings..." class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select id="listing-status-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select id="listing-category-filter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                        </select>
                        <button onclick="filterListings()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>

                    <!-- Listings Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="listings-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- Listings will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Users Section -->
        <section id="users-section" class="admin-section hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Users Management</h2>
                </div>
                <div class="p-6">
                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KYC Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- Users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Upsells Section -->
        <section id="upsells-section" class="admin-section hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Upsells Management</h2>
                </div>
                <div class="p-6">
                    <!-- Upsells Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Listing</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="upsells-table-body" class="bg-white divide-y divide-gray-200">
                                <!-- Upsells will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Reports Section -->
        <section id="reports-section" class="admin-section hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Revenue Overview</h2>
                    </div>
                    <div class="p-6">
                        <canvas id="revenue-chart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Listing Statistics</h2>
                    </div>
                    <div class="p-6">
                        <canvas id="listings-chart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Ad Creation Modal -->
<div id="ad-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create New Ad</h3>
            <form id="ad-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ad Type</label>
                    <select id="ad-type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select Type</option>
                        <option value="banner">Banner Ad</option>
                        <option value="sponsored">Sponsored Ad</option>
                        <option value="featured">Featured Ad</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" id="ad-title" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea id="ad-content" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                    <input type="url" id="ad-image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (days)</label>
                    <input type="number" id="ad-duration" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAdModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create Ad</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize admin dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadOverviewData();
    loadCategories();
    startLiveUpdates();
});

// Live updates configuration
let liveUpdateInterval;
let isLiveUpdatesEnabled = true;
const UPDATE_INTERVAL = 30000; // 30 seconds

function startLiveUpdates() {
    // Clear any existing interval
    if (liveUpdateInterval) {
        clearInterval(liveUpdateInterval);
    }
    
    // Start live updates
    liveUpdateInterval = setInterval(() => {
        if (isLiveUpdatesEnabled) {
            updateLiveData();
        }
    }, UPDATE_INTERVAL);
    
    console.log('Live updates started - refreshing every 30 seconds');
}

function stopLiveUpdates() {
    if (liveUpdateInterval) {
        clearInterval(liveUpdateInterval);
        liveUpdateInterval = null;
    }
    console.log('Live updates stopped');
}

function toggleLiveUpdates() {
    isLiveUpdatesEnabled = !isLiveUpdatesEnabled;
    const button = document.getElementById('live-toggle-btn');
    if (button) {
        button.textContent = isLiveUpdatesEnabled ? 'Pause Live Updates' : 'Resume Live Updates';
        button.className = isLiveUpdatesEnabled ? 
            'bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 text-sm' :
            'bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm';
    }
    
    if (isLiveUpdatesEnabled) {
        updateLiveData(); // Immediate update when resuming
    }
}

async function updateLiveData() {
    const currentSection = document.querySelector('.admin-section:not(.hidden)').id.replace('-section', '');
    
    try {
        // Show loading indicator
        showLoadingIndicator();
        
        switch(currentSection) {
            case 'overview':
                await loadOverviewData(true); // true = silent update
                break;
            case 'reports':
                await loadReports(true); // true = silent update
                break;
            case 'listings':
                await loadListings(true); // true = silent update
                break;
            case 'ads':
                await loadAds(true); // true = silent update
                break;
            case 'users':
                await loadUsers(true); // true = silent update
                break;
            case 'upsells':
                await loadUpsells(true); // true = silent update
                break;
        }
        
        // Update last refresh time
        updateLastRefreshTime();
        
    } catch (error) {
        console.error('Error updating live data:', error);
    } finally {
        hideLoadingIndicator();
    }
}

function showLoadingIndicator() {
    let indicator = document.getElementById('live-update-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'live-update-indicator';
        indicator.className = 'fixed top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-md text-sm z-50';
        indicator.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-2"></i>Updating...';
        document.body.appendChild(indicator);
    }
}

function hideLoadingIndicator() {
    const indicator = document.getElementById('live-update-indicator');
    if (indicator) {
        setTimeout(() => indicator.remove(), 500);
    }
}

function updateLastRefreshTime() {
    let timeDisplay = document.getElementById('last-refresh-time');
    if (!timeDisplay) {
        timeDisplay = document.createElement('div');
        timeDisplay.id = 'last-refresh-time';
        timeDisplay.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-3 py-1 rounded-md text-xs z-40';
        document.body.appendChild(timeDisplay);
    }
    
    const now = new Date();
    timeDisplay.textContent = `Last updated: ${now.toLocaleTimeString()}`;
}

// Navigation
function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.admin-section').forEach(el => el.classList.add('hidden'));
    
    // Show selected section
    document.getElementById(section + '-section').classList.remove('hidden');
    
    // Update navigation
    document.querySelectorAll('.admin-nav-item').forEach(el => {
        el.classList.remove('text-blue-600', 'border-blue-600');
        el.classList.add('text-gray-500', 'border-transparent');
    });
    
    const activeNav = document.querySelector(`[data-section="${section}"]`);
    activeNav.classList.remove('text-gray-500', 'border-transparent');
    activeNav.classList.add('text-blue-600', 'border-blue-600');
    
    // Load section data
    switch(section) {
        case 'overview':
            loadOverviewData();
            break;
        case 'ads':
            loadAds();
            break;
        case 'listings':
            loadListings();
            break;
        case 'users':
            loadUsers();
            break;
        case 'upsells':
            loadUpsells();
            break;
        case 'reports':
            loadReports();
            break;
    }
}

// Load overview data
async function loadOverviewData(silent = false) {
    try {
        // Get real analytics data
        const [overview, revenueData, jobsData] = await Promise.all([
            window.apiService.getAnalyticsOverview(),
            window.apiService.getAnalyticsRevenue({ start_date: getDateRange(30), end_date: getDateRange(0) }),
            window.apiService.getAnalyticsJobs({ start_date: getDateRange(30), end_date: getDateRange(0) })
        ]);
        
        // Update overview cards with real data
        if (overview.data) {
            updateOverviewCards(overview.data);
        }
        
        // Update recent activity with real data
        if (jobsData.data && jobsData.data.most_active) {
            updateRecentActivity(jobsData.data.most_active);
        }
        
        if (!silent) {
            console.log('Overview data loaded successfully');
        }
        
    } catch (error) {
        console.error('Error loading overview data:', error);
        
        // Show error message instead of hardcoded data
        if (!silent) {
            document.getElementById('total-listings').textContent = 'Error';
            document.getElementById('active-ads').textContent = 'Error';
            document.getElementById('total-users').textContent = 'Error';
            document.getElementById('total-revenue').textContent = 'Error';
            
            const activityContainer = document.getElementById('recent-activity');
            if (activityContainer) {
                activityContainer.innerHTML = '<div class="text-center py-4 text-gray-500">Error loading activity data</div>';
            }
        }
    }
}

function updateOverviewCards(data) {
    // Update with smooth animations
    animateValue('total-listings', data.total_jobs || 0);
    animateValue('active-ads', data.active_jobs || 0);
    animateValue('total-users', data.total_candidates || 0);
    
    // Update revenue breakdowns
    if (data.revenue) {
        animateValue('total-revenue', data.revenue.total_all_time || 0, true);
        updateRevenueBreakdowns(data.revenue);
    } else {
        // Fallback for legacy data
        animateValue('total-revenue', data.total_revenue || 0, true);
    }
}

function updateRevenueBreakdowns(revenue) {
    // Update detailed revenue sections if they exist
    const elements = {
        'this-month-revenue': revenue.this_month || 0,
        'this-week-revenue': revenue.this_week || 0,
        'today-revenue': revenue.today || 0,
        'total-ad-revenue': revenue.total_ad_revenue || 0,
        'banner-revenue': revenue.banner_revenue || 0,
        'affiliate-revenue': revenue.affiliate_revenue || 0,
        'other-revenue': revenue.other_revenue || 0
    };
    
    Object.entries(elements).forEach(([elementId, value]) => {
        const element = document.getElementById(elementId);
        if (element) {
            animateValue(elementId, value, true);
        }
    });
    
    // Update current month label
    const monthLabel = document.getElementById('current-month-label');
    if (monthLabel) {
        const now = new Date();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                          'July', 'August', 'September', 'October', 'November', 'December'];
        monthLabel.textContent = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;
    }
}

function animateValue(elementId, endValue, isCurrency = false) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
    const duration = 1000; // 1 second animation
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = Math.floor(startValue + (endValue - startValue) * progress);
        element.textContent = isCurrency ? `$${currentValue.toLocaleString()}` : currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

function updateRecentActivity(mostActiveJobs) {
    const activityContainer = document.getElementById('recent-activity');
    if (!activityContainer || !mostActiveJobs.length) return;
    
    const activityHtml = mostActiveJobs.slice(0, 5).map(job => `
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-2 mr-3">
                    <i class="fas fa-briefcase text-green-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-900">New job: "${job.title}"</p>
                    <p class="text-xs text-gray-500">${job.category?.name || 'Uncategorized'}</p>
                </div>
            </div>
            <span class="text-xs text-gray-500">${formatRelativeTime(job.created_at)}</span>
        </div>
    `).join('');
    
    activityContainer.innerHTML = activityHtml;
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins} minutes ago`;
    if (diffHours < 24) return `${diffHours} hours ago`;
    return `${diffDays} days ago`;
}

function getDateRange(daysAgo) {
    const date = new Date();
    date.setDate(date.getDate() - daysAgo);
    return date.toISOString().split('T')[0];
}


// Load ads
async function loadAds(silent = false) {
    try {
        const ads = await window.apiService.getAds();
        const adsHtml = ads.map(ad => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img src="${ad.image_url || '/placeholder.png'}" alt="${ad.title}" class="h-10 w-10 rounded mr-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${ad.title}</div>
                            <div class="text-sm text-gray-500">${ad.content ? ad.content.substring(0, 50) + '...' : ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${ad.type || 'banner'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(ad.status)}">
                        ${ad.status || 'active'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(ad.start_date)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(ad.end_date)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="editAd(${ad.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button onclick="deleteAd(${ad.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('ads-table-body').innerHTML = adsHtml;
        
        if (!silent) {
            console.log('Ads data loaded successfully');
        }
    } catch (error) {
        console.error('Error loading ads:', error);
        if (!silent) {
            document.getElementById('ads-table-body').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Error loading ads</td></tr>';
        }
    }
}

// Load listings
async function loadListings(silent = false) {
    try {
        const listings = await window.apiService.getListings();
        const listingsHtml = listings.map(listing => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img src="${listing.images && listing.images[0] ? listing.images[0] : '/placeholder.png'}" alt="${listing.title}" class="h-10 w-10 rounded mr-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${listing.title}</div>
                            <div class="text-sm text-gray-500">${listing.price ? '$' + listing.price : ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${listing.category ? listing.category.name : 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${listing.customer ? listing.customer.name : 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(listing.status)}">
                        ${listing.status || 'active'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(listing.created_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewListing(${listing.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                    <button onclick="editListing(${listing.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button onclick="deleteListing(${listing.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('listings-table-body').innerHTML = listingsHtml;
        
        if (!silent) {
            console.log('Listings data loaded successfully');
        }
    } catch (error) {
        console.error('Error loading listings:', error);
        if (!silent) {
            document.getElementById('listings-table-body').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Error loading listings</td></tr>';
        }
    }
}

// Load categories for filters
async function loadCategories() {
    try {
        const categories = await window.apiService.getCategories();
        const categoryFilter = document.getElementById('listing-category-filter');
        if (categoryFilter) {
            const options = categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            categoryFilter.innerHTML = '<option value="">All Categories</option>' + options;
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Helper functions
function getActivityColor(type) {
    const colors = {
        listing: 'green',
        ad: 'blue',
        user: 'purple'
    };
    return colors[type] || 'gray';
}

function getActivityIcon(type) {
    const icons = {
        listing: 'list',
        ad: 'ad',
        user: 'user'
    };
    return icons[type] || 'circle';
}

function getStatusColor(status) {
    const colors = {
        active: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        expired: 'bg-red-100 text-red-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
}

// Ad modal functions
function createNewAd() {
    document.getElementById('ad-modal').classList.remove('hidden');
}

function closeAdModal() {
    document.getElementById('ad-modal').classList.add('hidden');
    document.getElementById('ad-form').reset();
}

// Ad form submission
document.getElementById('ad-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const adData = {
        type: document.getElementById('ad-type').value,
        title: document.getElementById('ad-title').value,
        content: document.getElementById('ad-content').value,
        image_url: document.getElementById('ad-image').value,
        duration: parseInt(document.getElementById('ad-duration').value)
    };
    
    try {
        await window.apiService.createAd(adData);
        closeAdModal();
        loadAds();
        showNotification('Ad created successfully!', 'success');
    } catch (error) {
        console.error('Error creating ad:', error);
        showNotification('Error creating ad', 'error');
    }
});

// Load users with live data
async function loadUsers(silent = false) {
    try {
        const [customers, candidates] = await Promise.all([
            window.apiService.getCustomers(),
            window.apiService.getCandidateProfiles()
        ]);
        
        let usersHtml = '';
        
        // Combine customers and candidates for comprehensive user view
        const allUsers = [];
        
        if (customers && customers.data) {
            customers.data.forEach(customer => {
                allUsers.push({
                    ...customer,
                    type: 'customer',
                    kyc_status: customer.kyc_status || 'pending'
                });
            });
        }
        
        if (candidates && candidates.data) {
            candidates.data.forEach(candidate => {
                allUsers.push({
                    ...candidate,
                    type: 'candidate',
                    kyc_status: candidate.verification_status || 'pending'
                });
            });
        }
        
        usersHtml = allUsers.slice(0, 20).map(user => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img src="${user.avatar || '/placeholder.png'}" alt="${user.name || 'User'}" class="h-8 w-8 rounded-full mr-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${user.name || 'Unknown'}</div>
                            <div class="text-sm text-gray-500">${user.email || 'No email'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.email || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getKycStatusColor(user.kyc_status)}">
                        ${user.kyc_status || 'pending'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.listings_count || 0}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(user.created_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewUser(${user.id}, '${user.type}')" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                    <button onclick="editUser(${user.id}, '${user.type}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button onclick="suspendUser(${user.id}, '${user.type}')" class="text-red-600 hover:text-red-900">Suspend</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('users-table-body').innerHTML = usersHtml || '<tr><td colspan="6" class="text-center py-4 text-gray-500">No users found</td></tr>';
        
        if (!silent) {
            console.log('Users data loaded successfully');
        }
    } catch (error) {
        console.error('Error loading users:', error);
        if (!silent) {
            document.getElementById('users-table-body').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Error loading users</td></tr>';
        }
    }
}

function getKycStatusColor(status) {
    const colors = {
        verified: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

// Load upsells with live data
async function loadUpsells(silent = false) {
    try {
        const [jobUpsells, candidateUpsells, listingUpsells] = await Promise.all([
            window.apiService.getJobUpsells(),
            window.apiService.getCandidateUpsells(),
            window.apiService.getMyUpsells()
        ]);
        
        let upsellsHtml = '';
        const allUpsells = [];
        
        // Process job upsells
        if (jobUpsells && jobUpsells.data) {
            jobUpsells.data.forEach(upsell => {
                allUpsells.push({
                    ...upsell,
                    upsell_type: 'job',
                    related_item: upsell.listing?.title || 'Unknown Job',
                    user: upsell.customer?.name || 'Unknown User'
                });
            });
        }
        
        // Process candidate upsells
        if (candidateUpsells && candidateUpsells.data) {
            candidateUpsells.data.forEach(upsell => {
                allUpsells.push({
                    ...upsell,
                    upsell_type: 'candidate',
                    related_item: upsell.candidate_profile?.title || 'Unknown Profile',
                    user: upsell.customer?.name || 'Unknown User'
                });
            });
        }
        
        // Process listing upsells
        if (listingUpsells && listingUpsells.data) {
            listingUpsells.data.forEach(upsell => {
                allUpsells.push({
                    ...upsell,
                    upsell_type: 'listing',
                    related_item: upsell.listing?.title || 'Unknown Listing',
                    user: upsell.customer?.name || 'Unknown User'
                });
            });
        }
        
        // Sort by creation date (newest first)
        allUpsells.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        
        upsellsHtml = allUpsells.slice(0, 20).map(upsell => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${upsell.related_item}</div>
                    <div class="text-sm text-gray-500">${upsell.upsell_type} upsell</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                        ${upsell.upsell_type || 'featured'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${upsell.user}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(upsell.start_date || upsell.created_at)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(upsell.expires_at || upsell.end_date)}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getUpsellStatusColor(upsell.status)}">
                        ${upsell.status || 'active'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="viewUpsell(${upsell.id}, '${upsell.upsell_type}')" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                    <button onclick="editUpsell(${upsell.id}, '${upsell.upsell_type}')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button onclick="cancelUpsell(${upsell.id}, '${upsell.upsell_type}')" class="text-red-600 hover:text-red-900">Cancel</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('upsells-table-body').innerHTML = upsellsHtml || '<tr><td colspan="7" class="text-center py-4 text-gray-500">No upsells found</td></tr>';
        
        if (!silent) {
            console.log('Upsells data loaded successfully');
        }
    } catch (error) {
        console.error('Error loading upsells:', error);
        if (!silent) {
            document.getElementById('upsells-table-body').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-500">Error loading upsells</td></tr>';
        }
    }
}

function getUpsellStatusColor(status) {
    const colors = {
        active: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        expired: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

// Load reports with live charts
async function loadReports(silent = false) {
    try {
        // Get analytics data for charts and overview
        const [revenueData, jobsData, overviewData] = await Promise.all([
            window.apiService.getAnalyticsRevenue({ start_date: getDateRange(30), end_date: getDateRange(0) }),
            window.apiService.getAnalyticsJobs({ start_date: getDateRange(30), end_date: getDateRange(0) }),
            window.apiService.getAnalyticsOverview()
        ]);
        
        // Update overview cards
        if (overviewData.data) {
            updateOverviewCards(overviewData.data);
        }
        
        // Update revenue chart
        if (revenueData.data && revenueData.data.revenue_data) {
            updateRevenueChart(revenueData.data.revenue_data);
        }
        
        // Update jobs chart
        if (jobsData.data && jobsData.data.job_trends) {
            updateJobsChart(jobsData.data.job_trends);
        }
        
        // Update recent activity with real data
        if (jobsData.data && jobsData.data.most_active) {
            updateRecentActivity(jobsData.data.most_active);
        }
        
        if (!silent) {
            console.log('Reports data loaded successfully');
        }
        
    } catch (error) {
        console.error('Error loading reports:', error);
        if (!silent) {
            // Show error message in charts
            showChartError('revenue-chart');
            showChartError('listings-chart');
        }
    }
}

// Chart instances
let revenueChartInstance = null;
let jobsChartInstance = null;

function updateRevenueChart(revenueData) {
    const ctx = document.getElementById('revenue-chart');
    if (!ctx) return;
    
    const labels = revenueData.map(item => item.period);
    const data = revenueData.map(item => item.total_amount);
    
    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }
    
    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateJobsChart(jobsData) {
    const ctx = document.getElementById('listings-chart');
    if (!ctx) return;
    
    const labels = jobsData.map(item => item.date);
    const data = jobsData.map(item => item.job_count);
    
    if (jobsChartInstance) {
        jobsChartInstance.destroy();
    }
    
    jobsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jobs Posted',
                data: data,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function showChartError(chartId) {
    const ctx = document.getElementById(chartId);
    if (!ctx) return;
    
    // Clear any existing chart
    if (chartId === 'revenue-chart' && revenueChartInstance) {
        revenueChartInstance.destroy();
        revenueChartInstance = null;
    } else if (chartId === 'listings-chart' && jobsChartInstance) {
        jobsChartInstance.destroy();
        jobsChartInstance = null;
    }
    
    // Show error message
    ctx.getContext('2d').font = '16px Arial';
    ctx.fillStyle = '#666';
    ctx.textAlign = 'center';
    ctx.fillText('Error loading chart data', ctx.width / 2, ctx.height / 2);
}

function filterAds() {
    console.log('Filtering ads...');
}

function filterListings() {
    console.log('Filtering listings...');
}

function editAd(id) {
    console.log('Editing ad:', id);
}

function deleteAd(id) {
    console.log('Deleting ad:', id);
}

function viewListing(id) {
    console.log('Viewing listing:', id);
}

function editListing(id) {
    console.log('Editing listing:', id);
}

function deleteListing(id) {
    console.log('Deleting listing:', id);
}

function showNotification(message, type) {
    // Simple notification implementation
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
