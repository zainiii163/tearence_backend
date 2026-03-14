@extends('layouts.app')

@section('title', 'Browse Funding Projects')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg p-8 mb-8">
        <h1 class="text-4xl font-bold mb-2">Fuel Ideas. Fund Impact.</h1>
        <p class="text-lg mb-6">Discover projects seeking funding or post your own idea to connect with funders worldwide</p>
        <a href="{{ route('funding.create') }}" class="inline-block bg-white text-blue-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition">
            Start a Project
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Filters</h2>

        <form id="filterForm" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="categoryFilter" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <option value="technology">Technology</option>
                    <option value="creative_arts">Creative Arts</option>
                    <option value="community_social_impact">Community & Social Impact</option>
                    <option value="health_wellness">Health & Wellness</option>
                    <option value="education">Education</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Funding Model</label>
                <select id="modelFilter" name="funding_model" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                    <option value="">All Models</option>
                    <option value="donation">Donation</option>
                    <option value="reward">Reward-Based</option>
                    <option value="equity">Equity</option>
                    <option value="loan">Loan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                <select id="sortFilter" name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500">
                    <option value="latest">Latest</option>
                    <option value="trending">Trending</option>
                    <option value="featured">Featured</option>
                    <option value="ending_soon">Ending Soon</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Projects Grid -->
    <div id="projectsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Projects will be loaded here -->
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">Loading projects...</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="mt-8 flex justify-center gap-2">
        <!-- Pagination will be loaded here -->
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

async function loadProjects(page = 1) {
    const params = new URLSearchParams({
        page: page,
        per_page: 12,
        ...currentFilters
    });

    try {
        const response = await fetch(`/api/v1/funding?${params}`);
        const data = await response.json();

        if (data.success) {
            displayProjects(data.data.data);
            displayPagination(data.data);
            currentPage = page;
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        document.getElementById('projectsContainer').innerHTML = '<div class="col-span-full text-center text-red-500">Error loading projects</div>';
    }
}

function displayProjects(projects) {
    const container = document.getElementById('projectsContainer');

    if (projects.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500">No projects found</p></div>';
        return;
    }

    container.innerHTML = projects.map(project => `
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="relative">
                <img src="${project.cover_image}" alt="${project.title}" class="w-full h-48 object-cover">
                ${project.is_featured ? '<div class="absolute top-2 right-2 bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-bold">Featured</div>' : ''}
                ${project.is_promoted ? '<div class="absolute top-2 right-2 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold">Promoted</div>' : ''}
                ${project.is_sponsored ? '<div class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">Sponsored</div>' : ''}
            </div>

            <div class="p-4">
                <h3 class="text-lg font-bold mb-1">${project.title}</h3>
                <p class="text-sm text-gray-600 mb-3">${project.tagline || ''}</p>

                <div class="mb-3">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>${project.currency} ${parseFloat(project.amount_raised).toLocaleString()} raised</span>
                        <span>${Math.round((project.amount_raised / project.funding_goal) * 100)}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min((project.amount_raised / project.funding_goal) * 100, 100)}%"></div>
                    </div>
                </div>

                <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                    <span>${project.backer_count} backers</span>
                    <span>${project.category}</span>
                </div>

                <a href="/funding/${project.id}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition">
                    View Details
                </a>
            </div>
        </div>
    `).join('');
}

function displayPagination(data) {
    const container = document.getElementById('paginationContainer');
    const { current_page, last_page } = data;

    if (last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Previous
    if (current_page > 1) {
        html += `<button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" onclick="loadProjects(${current_page - 1})">← Previous</button>`;
    }

    // Page numbers
    for (let i = Math.max(1, current_page - 2); i <= Math.min(last_page, current_page + 2); i++) {
        html += `<button class="px-4 py-2 ${i === current_page ? 'bg-blue-600 text-white' : 'border border-gray-300 hover:bg-gray-100'} rounded-lg" onclick="loadProjects(${i})">${i}</button>`;
    }

    // Next
    if (current_page < last_page) {
        html += `<button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100" onclick="loadProjects(${current_page + 1})">Next →</button>`;
    }

    container.innerHTML = html;
}

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', (e) => {
    e.preventDefault();
    currentFilters = {
        category: document.getElementById('categoryFilter').value,
        funding_model: document.getElementById('modelFilter').value,
        sort: document.getElementById('sortFilter').value
    };
    Object.keys(currentFilters).forEach(key => !currentFilters[key] && delete currentFilters[key]);
    loadProjects(1);
});

// Initial load
loadProjects();
</script>
@endsection
