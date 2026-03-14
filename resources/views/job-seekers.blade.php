@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Find Top Talent</h1>
                <p class="text-xl mb-8">Browse qualified job seekers and find your perfect candidate</p>
                
                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
                    <form id="seekerSearchForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                                <input type="text" name="search" id="search" placeholder="Skills, profession..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input type="text" name="country" id="country" placeholder="Country" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                                <select name="experience_level" id="experience_level" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                                    <option value="">All Levels</option>
                                    <option value="entry">Entry Level</option>
                                    <option value="junior">Junior</option>
                                    <option value="mid">Mid Level</option>
                                    <option value="senior">Senior</option>
                                    <option value="executive">Executive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Education</label>
                                <select name="education_level" id="education_level" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-900">
                                    <option value="">All Levels</option>
                                    <option value="high_school">High School</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="bachelor">Bachelor's</option>
                                    <option value="master">Master's</option>
                                    <option value="phd">PhD</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remote_only" id="remote_only" 
                                           class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-gray-700">Remote Available</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                                    Search Candidates
                                </button>
                                <button type="button" id="clearFilters" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-8 bg-white border-b">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <div class="text-3xl font-bold text-purple-600" id="totalSeekers">-</div>
                    <div class="text-gray-600">Total Seekers</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600" id="featuredSeekers">-</div>
                    <div class="text-gray-600">Featured</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-blue-600" id="remoteSeekers">-</div>
                    <div class="text-gray-600">Remote Available</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-orange-600" id="avgExperience">-</div>
                    <div class="text-gray-600">Avg. Experience</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seekers Listing -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Job Seekers</h2>
                <div class="flex gap-4">
                    <select id="sortSeekers" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="recent">Most Recent</option>
                        <option value="most_viewed">Most Viewed</option>
                        <option value="featured">Featured</option>
                        <option value="experience">Experience</option>
                    </select>
                    <button id="createProfileBtn" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                        Create Profile
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="seekersGrid">
                <!-- Seekers will be loaded here -->
            </div>
            
            <div class="text-center mt-8">
                <button id="loadMoreSeekers" class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition duration-200 hidden">
                    Load More Candidates
                </button>
            </div>
        </div>
    </section>
</div>

<!-- Seeker Detail Modal -->
<div id="seekerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 w-full max-w-4xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalSeekerName"></h3>
                    <button onclick="closeSeekerModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalSeekerContent">
                    <!-- Seeker details will be loaded here -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="contactSeeker()" id="contactSeekerBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Contact Candidate
                </button>
                <button onclick="viewSeekerProfile()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    View Full Profile
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let currentSeeker = null;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadSeekerStats();
    loadSeekers();
    
    // Event listeners
    document.getElementById('seekerSearchForm').addEventListener('submit', searchSeekers);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('sortSeekers').addEventListener('change', loadSeekers);
    document.getElementById('loadMoreSeekers').addEventListener('click', loadMoreSeekers);
    document.getElementById('createProfileBtn').addEventListener('click', function() {
        window.location.href = '/jobs/create';
    });
});

// Load functions
async function loadSeekerStats() {
    try {
        const response = await fetch('/api/public/jobs/seekers/stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalSeekers').textContent = data.data.total_seekers;
            document.getElementById('featuredSeekers').textContent = data.data.featured_seekers;
            document.getElementById('remoteSeekers').textContent = data.data.remote_available;
            document.getElementById('avgExperience').textContent = '5+'; // Placeholder
        }
    } catch (error) {
        console.error('Error loading seeker stats:', error);
    }
}

async function loadSeekers(append = false) {
    try {
        const formData = new FormData(document.getElementById('seekerSearchForm'));
        const params = new URLSearchParams();
        
        // Add form data
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        // Add sort and pagination
        params.append('sort', document.getElementById('sortSeekers').value);
        params.append('page', append ? currentPage : 1);
        params.append('per_page', 12);
        
        const response = await fetch(`/api/public/jobs/seekers?${params}`);
        const data = await response.json();
        
        if (data.success) {
            const seekersGrid = document.getElementById('seekersGrid');
            
            if (!append) {
                seekersGrid.innerHTML = '';
                currentPage = 1;
            }
            
            data.data.data.forEach(seeker => {
                const seekerCard = createSeekerCard(seeker);
                seekersGrid.innerHTML += seekerCard;
            });
            
            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreSeekers');
            if (data.data.next_page_url) {
                loadMoreBtn.classList.remove('hidden');
                currentPage++;
            } else {
                loadMoreBtn.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading seekers:', error);
    }
}

// Create seeker card
function createSeekerCard(seeker) {
    const experienceLabel = seeker.experience_level ? seeker.experience_level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified';
    const educationLabel = seeker.education_level ? seeker.education_level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified';
    const isRemote = seeker.remote_availability ? '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Remote Available</span>' : '';
    const isFeatured = seeker.is_featured ? '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>' : '';
    
    return `
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer" onclick="showSeekerDetails(${seeker.id})">
            <div class="flex items-center mb-4">
                ${seeker.profile_photo ? `<img src="/storage/${seeker.profile_photo}" alt="${seeker.full_name}" class="w-16 h-16 object-cover rounded-full mr-4">` : '<div class="w-16 h-16 bg-gray-200 rounded-full mr-4 flex items-center justify-center"><span class="text-gray-500">👤</span></div>'}
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">${seeker.full_name}</h3>
                    <p class="text-gray-600">${seeker.profession}</p>
                    <p class="text-sm text-gray-500">${seeker.country}${seeker.city ? ', ' + seeker.city : ''}</p>
                </div>
            </div>
            
            <div class="flex gap-2 mb-4">
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${experienceLabel}</span>
                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">${educationLabel}</span>
                ${isRemote}
                ${isFeatured}
            </div>
            
            <p class="text-gray-600 mb-4 line-clamp-3">${seeker.bio || 'No bio available'}</p>
            
            ${seeker.key_skills ? `
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Key Skills:</p>
                <div class="flex flex-wrap gap-1">
                    ${seeker.key_skills.split(',').slice(0, 3).map(skill => 
                        `<span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">${skill.trim()}</span>`
                    ).join('')}
                    ${seeker.key_skills.split(',').length > 3 ? '<span class="text-xs text-gray-500">+' + (seeker.key_skills.split(',').length - 3) + ' more</span>' : ''}
                </div>
            </div>
            ` : ''}
            
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>${seeker.years_of_experience || 0} years experience</span>
                <span>${seeker.views_count || 0} views</span>
            </div>
        </div>
    `;
}

// Search and filter functions
function searchSeekers(e) {
    e.preventDefault();
    loadSeekers();
}

function clearFilters() {
    document.getElementById('seekerSearchForm').reset();
    loadSeekers();
}

function loadMoreSeekers() {
    loadSeekers(true);
}

// Modal functions
async function showSeekerDetails(seekerId) {
    try {
        const response = await fetch(`/api/public/jobs/seekers/${seekerId}`);
        const data = await response.json();
        
        if (data.success) {
            currentSeeker = data.data;
            document.getElementById('modalSeekerName').textContent = currentSeeker.full_name;
            
            const experienceLabel = currentSeeker.experience_level ? currentSeeker.experience_level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified';
            const educationLabel = currentSeeker.education_level ? currentSeeker.education_level.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified';
            
            document.getElementById('modalSeekerContent').innerHTML = `
                <div class="space-y-6">
                    <div class="flex items-center gap-6">
                        ${currentSeeker.profile_photo ? `<img src="/storage/${currentSeeker.profile_photo}" alt="${currentSeeker.full_name}" class="w-24 h-24 object-cover rounded-full">` : '<div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center"><span class="text-gray-500 text-3xl">👤</span></div>'}
                        <div>
                            <h4 class="text-2xl font-semibold">${currentSeeker.full_name}</h4>
                            <p class="text-lg text-gray-600">${currentSeeker.profession}</p>
                            <p class="text-gray-500">${currentSeeker.country}${currentSeeker.city ? ', ' + currentSeeker.city : ''}</p>
                            <div class="flex gap-2 mt-2">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${experienceLabel}</span>
                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">${educationLabel}</span>
                                ${currentSeeker.remote_availability ? '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Remote Available</span>' : ''}
                                ${currentSeeker.is_featured ? '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>' : ''}
                            </div>
                        </div>
                    </div>
                    
                    ${currentSeeker.bio ? `
                    <div>
                        <h5 class="font-semibold mb-2">About</h5>
                        <div class="text-gray-700">${currentSeeker.bio}</div>
                    </div>
                    ` : ''}
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h5 class="font-semibold mb-2">Experience</h5>
                            <p class="text-gray-700">${currentSeeker.years_of_experience || 0} years</p>
                        </div>
                        <div>
                            <h5 class="font-semibold mb-2">Desired Role</h5>
                            <p class="text-gray-700">${currentSeeker.desired_role || 'Not specified'}</p>
                        </div>
                        <div>
                            <h5 class="font-semibold mb-2">Work Type</h5>
                            <p class="text-gray-700">${currentSeeker.work_type ? currentSeeker.work_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified'}</p>
                        </div>
                        <div>
                            <h5 class="font-semibold mb-2">Salary Expectation</h5>
                            <p class="text-gray-700">${currentSeeker.salary_expectation || 'Negotiable'}</p>
                        </div>
                    </div>
                    
                    ${currentSeeker.key_skills ? `
                    <div>
                        <h5 class="font-semibold mb-2">Key Skills</h5>
                        <div class="flex flex-wrap gap-2">
                            ${currentSeeker.key_skills.split(',').map(skill => 
                                `<span class="bg-gray-100 text-gray-700 text-sm px-3 py-1 rounded-full">${skill.trim()}</span>`
                            ).join('')}
                        </div>
                    </div>
                    ` : ''}
                    
                    ${currentSeeker.industries_interested ? `
                    <div>
                        <h5 class="font-semibold mb-2">Industries Interested</h5>
                        <div class="flex flex-wrap gap-2">
                            ${currentSeeker.industries_interested.split(',').map(industry => 
                                `<span class="bg-blue-100 text-blue-700 text-sm px-3 py-1 rounded-full">${industry.trim()}</span>`
                            ).join('')}
                        </div>
                    </div>
                    ` : ''}
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${currentSeeker.portfolio_link ? `
                        <div>
                            <h5 class="font-semibold mb-2">Portfolio</h5>
                            <a href="${currentSeeker.portfolio_link}" target="_blank" class="text-blue-600 hover:underline">View Portfolio</a>
                        </div>
                        ` : ''}
                        ${currentSeeker.linkedin_link ? `
                        <div>
                            <h5 class="font-semibold mb-2">LinkedIn</h5>
                            <a href="${currentSeeker.linkedin_link}" target="_blank" class="text-blue-600 hover:underline">View LinkedIn</a>
                        </div>
                        ` : ''}
                        ${currentSeeker.cv_file ? `
                        <div>
                            <h5 class="font-semibold mb-2">Resume/CV</h5>
                            <a href="/storage/${currentSeeker.cv_file}" target="_blank" class="text-blue-600 hover:underline">Download CV</a>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        <p>${currentSeeker.views_count || 0} profile views</p>
                        <p>Joined ${new Date(currentSeeker.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('seekerModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading seeker details:', error);
    }
}

function closeSeekerModal() {
    document.getElementById('seekerModal').classList.add('hidden');
    currentSeeker = null;
}

function contactSeeker() {
    if (!currentSeeker) return;
    // Implementation for contacting seeker
    alert('Contact feature would be implemented here. This could open a messaging modal or redirect to a contact form.');
}

function viewSeekerProfile() {
    if (!currentSeeker) return;
    // Implementation for viewing full profile
    window.open(`/job-seekers/${currentSeeker.id}`, '_blank');
}
</script>
@endsection
