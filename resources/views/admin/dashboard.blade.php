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
                            <p class="text-sm font-medium text-gray-600">Revenue</p>
                            <p class="text-2xl font-bold text-gray-900" id="total-revenue">-</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-dollar-sign text-yellow-600"></i>
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
});

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
async function loadOverviewData() {
    try {
        const listings = await window.apiService.getFeaturedListings();
        const ads = await window.apiService.getAds();
        
        document.getElementById('total-listings').textContent = listings.length || 0;
        document.getElementById('active-ads').textContent = ads.filter(ad => ad.status === 'active').length || 0;
        document.getElementById('total-users').textContent = '1,234'; // Mock data
        document.getElementById('total-revenue').textContent = '$12,345'; // Mock data
        
        // Load recent activity
        const recentActivity = [
            { type: 'listing', message: 'New listing created: "iPhone 13 Pro Max"', time: '2 minutes ago' },
            { type: 'ad', message: 'Banner ad purchased by user John Doe', time: '15 minutes ago' },
            { type: 'user', message: 'New user registered: jane@example.com', time: '1 hour ago' },
        ];
        
        const activityHtml = recentActivity.map(activity => `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center">
                    <div class="bg-${getActivityColor(activity.type)}-100 rounded-full p-2 mr-3">
                        <i class="fas fa-${getActivityIcon(activity.type)} text-${getActivityColor(activity.type)}-600 text-sm"></i>
                    </div>
                    <p class="text-sm text-gray-900">${activity.message}</p>
                </div>
                <span class="text-xs text-gray-500">${activity.time}</span>
            </div>
        `).join('');
        
        document.getElementById('recent-activity').innerHTML = activityHtml;
    } catch (error) {
        console.error('Error loading overview data:', error);
    }
}

// Load ads
async function loadAds() {
    try {
        const ads = await window.apiService.getAds();
        const adsHtml = ads.map(ad => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img src="${ad.image_url || '/placeholder.jpg'}" alt="${ad.title}" class="h-10 w-10 rounded mr-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${ad.title}</div>
                            <div class="text-sm text-gray-500">${ad.content.substring(0, 50)}...</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${ad.type}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(ad.status)}">
                        ${ad.status}
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
    } catch (error) {
        console.error('Error loading ads:', error);
    }
}

// Load listings
async function loadListings() {
    try {
        const listings = await window.apiService.getListings();
        const listingsHtml = listings.map(listing => `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img src="${listing.images[0] || '/placeholder.jpg'}" alt="${listing.title}" class="h-10 w-10 rounded mr-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${listing.title}</div>
                            <div class="text-sm text-gray-500">${listing.price ? '$' + listing.price : ''}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${listing.category.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${listing.user.name}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(listing.status)}">
                        ${listing.status}
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
    } catch (error) {
        console.error('Error loading listings:', error);
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

// Placeholder functions for other sections
function loadUsers() {
    console.log('Loading users...');
}

function loadUpsells() {
    console.log('Loading upsells...');
}

function loadReports() {
    console.log('Loading reports...');
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
