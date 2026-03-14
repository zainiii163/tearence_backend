@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Find Your Next Opportunity</h1>
                <p class="text-xl mb-8">Search Global Jobs. Post Vacancies. Connect With Talent Worldwide.</p>
                
                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
                    <form id="jobSearchForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title / Keywords</label>
                                <input type="text" name="search" id="search" placeholder="e.g. Software Engineer" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <input type="text" name="location" id="location" placeholder="City or Country" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category_id" id="category_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Work Type</label>
                                <select name="work_type" id="work_type" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                                    <option value="">All Types</option>
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="temporary">Temporary</option>
                                    <option value="internship">Internship</option>
                                    <option value="remote">Remote</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remote_only" id="remote_only" 
                                           class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-gray-700">Remote Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="urgent_only" id="urgent_only" 
                                           class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-gray-700">Urgent Only</span>
                                </label>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    Search Jobs
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
                    <div class="text-3xl font-bold text-blue-600" id="totalJobs">-</div>
                    <div class="text-gray-600">Total Jobs</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600" id="featuredJobs">-</div>
                    <div class="text-gray-600">Featured Jobs</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-orange-600" id="urgentJobs">-</div>
                    <div class="text-gray-600">Urgent Jobs</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-purple-600" id="totalCategories">-</div>
                    <div class="text-gray-600">Categories</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Browse by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="categoriesGrid">
                <!-- Categories will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Jobs Listing -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Latest Jobs</h2>
                <div class="flex gap-4">
                    <select id="sortJobs" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="recent">Most Recent</option>
                        <option value="salary_high">Highest Salary</option>
                        <option value="most_viewed">Most Viewed</option>
                        <option value="trending">Trending</option>
                    </select>
                    <button id="postJobBtn" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                        Post a Job
                    </button>
                </div>
            </div>
            
            <div class="grid gap-6" id="jobsGrid">
                <!-- Jobs will be loaded here -->
            </div>
            
            <div class="text-center mt-8">
                <button id="loadMoreJobs" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition duration-200 hidden">
                    Load More Jobs
                </button>
            </div>
        </div>
    </section>

    <!-- Job Seekers Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Featured Job Seekers</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="seekersGrid">
                <!-- Job seekers will be loaded here -->
            </div>
        </div>
    </section>
</div>

<!-- Job Detail Modal -->
<div id="jobModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 w-full max-w-4xl">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-2xl font-bold text-gray-900" id="modalJobTitle"></h3>
                    <button onclick="closeJobModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalJobContent">
                    <!-- Job details will be loaded here -->
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button onclick="applyForJob()" id="applyJobBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Apply Now
                </button>
                <button onclick="saveJob()" id="saveJobBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Job
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Application Modal -->
<div id="applicationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 w-full max-w-2xl">
            <form id="applicationForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">Apply for Job</h3>
                        <button onclick="closeApplicationModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone (Optional)</label>
                            <input type="tel" name="phone" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter</label>
                            <textarea name="cover_letter" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">CV/Resume (Optional)</label>
                            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Submit Application
                    </button>
                    <button type="button" onclick="closeApplicationModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Post Job Modal -->
<div id="postJobModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 w-full max-w-4xl max-h-screen overflow-y-auto">
            <form id="postJobForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">Post a Job</h3>
                        <button onclick="closePostJobModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Step 1: Posting Type -->
                    <div id="postingStep1" class="space-y-4">
                        <h4 class="text-lg font-semibold">Step 1: Select Posting Type</h4>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="posting_type" value="employer" checked class="mr-3">
                                <div>
                                    <div class="font-medium">I am an Employer — Post a Vacancy</div>
                                    <div class="text-sm text-gray-600">Post job openings and find qualified candidates</div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="posting_type" value="seeker" class="mr-3">
                                <div>
                                    <div class="font-medium">I am a Job Seeker — Post My Job Search Profile</div>
                                    <div class="text-sm text-gray-600">Create a profile to let employers find you</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Step 2: Employer Form -->
                    <div id="postingStep2Employer" class="space-y-4 hidden">
                        <h4 class="text-lg font-semibold">Step 2: Job Details</h4>
                        <!-- Job Basics -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title*</label>
                                <input type="text" name="title" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="job_category_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Job Description*</label>
                            <textarea name="description" rows="4" required 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
                                <textarea name="responsibilities" rows="3" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                                <textarea name="requirements" rows="3" 
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                        
                        <!-- Company Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company Name*</label>
                                <input type="text" name="company_name" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company Website</label>
                                <input type="url" name="company_website" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
                            <input type="file" name="company_logo" accept="image/*" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <!-- Location & Work Type -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country*</label>
                                <input type="text" name="country" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" name="city" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Work Type*</label>
                                <select name="work_type" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Type</option>
                                    <option value="full_time">Full Time</option>
                                    <option value="part_time">Part Time</option>
                                    <option value="contract">Contract</option>
                                    <option value="temporary">Temporary</option>
                                    <option value="internship">Internship</option>
                                    <option value="remote">Remote</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Compensation -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Salary Range</label>
                                <input type="text" name="salary_range" placeholder="e.g. $50,000 - $80,000" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select name="currency" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                    <option value="CAD">CAD</option>
                                    <option value="AUD">AUD</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
                            <textarea name="benefits" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        
                        <!-- Application Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application Method*</label>
                            <select name="application_method" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Method</option>
                                <option value="platform">Apply via Platform</option>
                                <option value="email">Apply via Email</option>
                                <option value="website">Apply via Website</option>
                            </select>
                        </div>
                        
                        <div id="emailField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application Email*</label>
                            <input type="email" name="application_email" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div id="websiteField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application URL*</label>
                            <input type="url" name="application_url" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <!-- Step 3: Upsell Options -->
                    <div id="postingStep3" class="space-y-4 hidden">
                        <h4 class="text-lg font-semibold">Step 3: Upgrade Your Listing</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 upsell-card" data-type="promoted">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">⭐</div>
                                    <h5 class="font-semibold">Promoted</h5>
                                    <div class="text-2xl font-bold text-blue-600">$29.99</div>
                                    <ul class="text-sm text-gray-600 mt-2">
                                        <li>✓ Highlighted listing</li>
                                        <li>✓ Above standard posts</li>
                                        <li>✓ "Promoted" badge</li>
                                        <li>✓ 2× more visibility</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 upsell-card border-blue-500 bg-blue-50" data-type="featured">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">🌟</div>
                                    <h5 class="font-semibold">Featured</h5>
                                    <div class="text-2xl font-bold text-blue-600">$79.99</div>
                                    <div class="text-sm text-orange-600 font-semibold">Most Popular</div>
                                    <ul class="text-sm text-gray-600 mt-2">
                                        <li>✓ Top of category pages</li>
                                        <li>✓ Larger listing card</li>
                                        <li>✓ Priority in search</li>
                                        <li>✓ Weekly email inclusion</li>
                                        <li>✓ "Featured" badge</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 upsell-card" data-type="sponsored">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">🚀</div>
                                    <h5 class="font-semibold">Sponsored</h5>
                                    <div class="text-2xl font-bold text-blue-600">$149.99</div>
                                    <ul class="text-sm text-gray-600 mt-2">
                                        <li>✓ Homepage placement</li>
                                        <li>✓ Category top placement</li>
                                        <li>✓ Homepage slider</li>
                                        <li>✓ Social media promotion</li>
                                        <li>✓ "Sponsored" badge</li>
                                        <li>✓ Maximum visibility</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="border rounded-lg p-4 cursor-pointer hover:border-blue-500 upsell-card" data-type="network_wide">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">👑</div>
                                    <h5 class="font-semibold">Network-Wide</h5>
                                    <div class="text-2xl font-bold text-blue-600">$299.99</div>
                                    <ul class="text-sm text-gray-600 mt-2">
                                        <li>✓ Multi-page appearance</li>
                                        <li>✓ Email newsletters</li>
                                        <li>✓ Push notifications</li>
                                        <li>✓ "Top Spotlight" badge</li>
                                        <li>✓ Ultimate visibility</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <input type="radio" name="upsell_type" value="none" checked class="mr-2">
                                <label class="text-sm">No upgrade - Post as standard listing (Free)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <div id="step1Buttons">
                        <button type="button" onclick="nextStep()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Next
                        </button>
                    </div>
                    <div id="step2Buttons" class="hidden">
                        <button type="button" onclick="nextStep()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Next
                        </button>
                        <button type="button" onclick="previousStep()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Previous
                        </button>
                    </div>
                    <div id="step3Buttons" class="hidden">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Post Job
                        </button>
                        <button type="button" onclick="previousStep()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Previous
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let currentJob = null;
let selectedUpsell = 'none';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadCategories();
    loadJobs();
    loadJobSeekers();
    
    // Event listeners
    document.getElementById('jobSearchForm').addEventListener('submit', searchJobs);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    document.getElementById('sortJobs').addEventListener('change', loadJobs);
    document.getElementById('loadMoreJobs').addEventListener('click', loadMoreJobs);
    document.getElementById('postJobBtn').addEventListener('click', openPostJobModal);
    document.getElementById('applicationForm').addEventListener('submit', submitApplication);
    document.getElementById('postJobForm').addEventListener('submit', submitJob);
    
    // Posting type change
    document.querySelectorAll('input[name="posting_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'employer') {
                document.getElementById('postingStep2Employer').classList.remove('hidden');
            } else {
                document.getElementById('postingStep2Employer').classList.add('hidden');
            }
        });
    });
    
    // Application method change
    document.querySelector('select[name="application_method"]').addEventListener('change', function() {
        document.getElementById('emailField').classList.add('hidden');
        document.getElementById('websiteField').classList.add('hidden');
        
        if (this.value === 'email') {
            document.getElementById('emailField').classList.remove('hidden');
        } else if (this.value === 'website') {
            document.getElementById('websiteField').classList.remove('hidden');
        }
    });
    
    // Upsell card selection
    document.querySelectorAll('.upsell-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.upsell-card').forEach(c => {
                c.classList.remove('border-blue-500', 'bg-blue-50');
            });
            this.classList.add('border-blue-500', 'bg-blue-50');
            selectedUpsell = this.dataset.type;
            document.querySelector('input[name="upsell_type"][value="' + selectedUpsell + '"]').checked = true;
        });
    });
});

// Load functions
async function loadStats() {
    try {
        const response = await fetch('/api/public/jobs/stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalJobs').textContent = data.data.total_jobs;
            document.getElementById('featuredJobs').textContent = data.data.featured_jobs;
            document.getElementById('urgentJobs').textContent = data.data.urgent_jobs;
            document.getElementById('totalCategories').textContent = data.data.total_categories;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch('/api/public/jobs/categories');
        const data = await response.json();
        
        if (data.success) {
            const categoriesGrid = document.getElementById('categoriesGrid');
            const categorySelect = document.getElementById('category_id');
            
            categoriesGrid.innerHTML = '';
            categorySelect.innerHTML = '<option value="">All Categories</option>';
            
            data.data.forEach(category => {
                // Add to grid
                const categoryCard = `
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer" onclick="filterByCategory(${category.id})">
                        <div class="text-3xl mb-3">${category.icon || '📁'}</div>
                        <h3 class="font-semibold text-lg">${category.name}</h3>
                        <p class="text-gray-600">${category.active_jobs_count || 0} jobs</p>
                    </div>
                `;
                categoriesGrid.innerHTML += categoryCard;
                
                // Add to select
                const option = `<option value="${category.id}">${category.name}</option>`;
                categorySelect.innerHTML += option;
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadJobs(append = false) {
    try {
        const formData = new FormData(document.getElementById('jobSearchForm'));
        const params = new URLSearchParams();
        
        // Add form data
        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }
        
        // Add sort and pagination
        params.append('sort', document.getElementById('sortJobs').value);
        params.append('page', append ? currentPage : 1);
        params.append('per_page', 12);
        
        const response = await fetch(`/api/public/jobs?${params}`);
        const data = await response.json();
        
        if (data.success) {
            const jobsGrid = document.getElementById('jobsGrid');
            
            if (!append) {
                jobsGrid.innerHTML = '';
                currentPage = 1;
            }
            
            data.data.data.forEach(job => {
                const jobCard = createJobCard(job);
                jobsGrid.innerHTML += jobCard;
            });
            
            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreJobs');
            if (data.data.next_page_url) {
                loadMoreBtn.classList.remove('hidden');
                currentPage++;
            } else {
                loadMoreBtn.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading jobs:', error);
    }
}

async function loadJobSeekers() {
    try {
        const response = await fetch('/api/public/jobs/seekers?per_page=6');
        const data = await response.json();
        
        if (data.success) {
            const seekersGrid = document.getElementById('seekersGrid');
            seekersGrid.innerHTML = '';
            
            data.data.data.forEach(seeker => {
                const seekerCard = createSeekerCard(seeker);
                seekersGrid.innerHTML += seekerCard;
            });
        }
    } catch (error) {
        console.error('Error loading job seekers:', error);
    }
}

// Create job card
function createJobCard(job) {
    const workTypeLabel = job.work_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    const isUrgent = job.is_urgent ? '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Urgent</span>' : '';
    const isFeatured = job.is_featured ? '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>' : '';
    
    return `
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer" onclick="showJobDetails(${job.id})">
            <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">${job.title}</h3>
                    <p class="text-gray-600 mb-2">${job.company_name} • ${job.country}${job.city ? ', ' + job.city : ''}</p>
                </div>
                ${job.company_logo ? `<img src="/storage/${job.company_logo}" alt="${job.company_name}" class="w-12 h-12 object-cover rounded">` : ''}
            </div>
            <div class="flex gap-2 mb-4">
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${workTypeLabel}</span>
                ${isUrgent}
                ${isFeatured}
            </div>
            <p class="text-gray-600 mb-4 line-clamp-2">${job.description}</p>
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>${job.views_count || 0} views • ${job.applications_count || 0} applications</span>
                <span>${new Date(job.created_at).toLocaleDateString()}</span>
            </div>
        </div>
    `;
}

// Create seeker card
function createSeekerCard(seeker) {
    return `
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer" onclick="showSeekerDetails(${seeker.id})">
            <div class="flex items-center mb-4">
                ${seeker.profile_photo ? `<img src="/storage/${seeker.profile_photo}" alt="${seeker.full_name}" class="w-16 h-16 object-cover rounded-full mr-4">` : '<div class="w-16 h-16 bg-gray-200 rounded-full mr-4 flex items-center justify-center"><span class="text-gray-500">👤</span></div>'}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${seeker.full_name}</h3>
                    <p class="text-gray-600">${seeker.profession}</p>
                    <p class="text-sm text-gray-500">${seeker.country}${seeker.city ? ', ' + seeker.city : ''}</p>
                </div>
            </div>
            <p class="text-gray-600 mb-4 line-clamp-2">${seeker.bio || 'No bio available'}</p>
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>${seeker.years_of_experience || 0} years experience</span>
                <span>${seeker.views_count || 0} views</span>
            </div>
        </div>
    `;
}

// Search and filter functions
function searchJobs(e) {
    e.preventDefault();
    loadJobs();
}

function clearFilters() {
    document.getElementById('jobSearchForm').reset();
    loadJobs();
}

function filterByCategory(categoryId) {
    document.getElementById('category_id').value = categoryId;
    loadJobs();
}

function loadMoreJobs() {
    loadJobs(true);
}

// Modal functions
async function showJobDetails(jobId) {
    try {
        const response = await fetch(`/api/public/jobs/${jobId}`);
        const data = await response.json();
        
        if (data.success) {
            currentJob = data.data;
            document.getElementById('modalJobTitle').textContent = currentJob.title;
            
            const workTypeLabel = currentJob.work_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            document.getElementById('modalJobContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        ${currentJob.company_logo ? `<img src="/storage/${currentJob.company_logo}" alt="${currentJob.company_name}" class="w-16 h-16 object-cover rounded">` : ''}
                        <div>
                            <h4 class="text-lg font-semibold">${currentJob.company_name}</h4>
                            <p class="text-gray-600">${currentJob.country}${currentJob.city ? ', ' + currentJob.city : ''}</p>
                            ${currentJob.company_website ? `<a href="${currentJob.company_website}" target="_blank" class="text-blue-600 hover:underline">Visit Website</a>` : ''}
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${workTypeLabel}</span>
                        ${currentJob.is_urgent ? '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Urgent</span>' : ''}
                        ${currentJob.is_featured ? '<span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>' : ''}
                    </div>
                    
                    <div>
                        <h5 class="font-semibold mb-2">Job Description</h5>
                        <div class="text-gray-700">${currentJob.description}</div>
                    </div>
                    
                    ${currentJob.responsibilities ? `
                    <div>
                        <h5 class="font-semibold mb-2">Responsibilities</h5>
                        <div class="text-gray-700">${currentJob.responsibilities}</div>
                    </div>
                    ` : ''}
                    
                    ${currentJob.requirements ? `
                    <div>
                        <h5 class="font-semibold mb-2">Requirements</h5>
                        <div class="text-gray-700">${currentJob.requirements}</div>
                    </div>
                    ` : ''}
                    
                    ${currentJob.skills_needed ? `
                    <div>
                        <h5 class="font-semibold mb-2">Skills Needed</h5>
                        <div class="text-gray-700">${currentJob.skills_needed}</div>
                    </div>
                    ` : ''}
                    
                    ${currentJob.salary_range ? `
                    <div>
                        <h5 class="font-semibold mb-2">Salary</h5>
                        <p class="text-gray-700">${currentJob.salary_range} ${currentJob.currency}</p>
                    </div>
                    ` : ''}
                    
                    ${currentJob.benefits ? `
                    <div>
                        <h5 class="font-semibold mb-2">Benefits</h5>
                        <div class="text-gray-700">${currentJob.benefits}</div>
                    </div>
                    ` : ''}
                    
                    <div class="text-sm text-gray-500">
                        <p>${currentJob.views_count || 0} views • ${currentJob.applications_count || 0} applications</p>
                        <p>Posted ${new Date(currentJob.created_at).toLocaleDateString()}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('jobModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading job details:', error);
    }
}

function closeJobModal() {
    document.getElementById('jobModal').classList.add('hidden');
    currentJob = null;
}

function openApplicationModal() {
    document.getElementById('applicationModal').classList.remove('hidden');
}

function closeApplicationModal() {
    document.getElementById('applicationModal').classList.add('hidden');
    document.getElementById('applicationForm').reset();
}

function openPostJobModal() {
    document.getElementById('postJobModal').classList.remove('hidden');
}

function closePostJobModal() {
    document.getElementById('postJobModal').classList.add('hidden');
    document.getElementById('postJobForm').reset();
    resetPostJobSteps();
}

function applyForJob() {
    if (!currentJob) return;
    closeJobModal();
    openApplicationModal();
}

async function saveJob() {
    if (!currentJob) return;
    
    try {
        const response = await fetch(`/api/jobs/${currentJob.id}/save`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Job saved successfully!');
        } else {
            alert(data.message || 'Error saving job');
        }
    } catch (error) {
        console.error('Error saving job:', error);
        alert('Error saving job');
    }
}

async function submitApplication(e) {
    e.preventDefault();
    
    if (!currentJob) return;
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(`/api/jobs/${currentJob.id}/apply`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Application submitted successfully!');
            closeApplicationModal();
            closeJobModal();
        } else {
            alert(data.message || 'Error submitting application');
        }
    } catch (error) {
        console.error('Error submitting application:', error);
        alert('Error submitting application');
    }
}

// Post job modal functions
let currentStep = 1;

function nextStep() {
    if (currentStep < 3) {
        document.getElementById(`postingStep${currentStep}`).classList.add('hidden');
        document.getElementById(`step${currentStep}Buttons`).classList.add('hidden');
        
        currentStep++;
        
        document.getElementById(`postingStep${currentStep}`).classList.remove('hidden');
        document.getElementById(`step${currentStep}Buttons`).classList.remove('hidden');
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.getElementById(`postingStep${currentStep}`).classList.add('hidden');
        document.getElementById(`step${currentStep}Buttons`).classList.add('hidden');
        
        currentStep--;
        
        document.getElementById(`postingStep${currentStep}`).classList.remove('hidden');
        document.getElementById(`step${currentStep}Buttons`).classList.remove('hidden');
    }
}

function resetPostJobSteps() {
    currentStep = 1;
    selectedUpsell = 'none';
    
    // Hide all steps except first
    for (let i = 2; i <= 3; i++) {
        document.getElementById(`postingStep${i}`).classList.add('hidden');
        document.getElementById(`step${i}Buttons`).classList.add('hidden');
    }
    
    document.getElementById('postingStep1').classList.remove('hidden');
    document.getElementById('step1Buttons').classList.remove('hidden');
    
    // Reset upsell selection
    document.querySelectorAll('.upsell-card').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
    });
}

async function submitJob(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const jobData = Object.fromEntries(formData);
    
    // Remove upsell_type from job data
    delete jobData.upsell_type;
    
    try {
        const response = await fetch('/api/jobs', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(jobData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Handle upsell if selected
            if (selectedUpsell !== 'none') {
                await createUpsell(data.data.id, 'job_listing', selectedUpsell);
            }
            
            alert('Job posted successfully!');
            closePostJobModal();
            loadJobs();
        } else {
            alert(data.message || 'Error posting job');
        }
    } catch (error) {
        console.error('Error posting job:', error);
        alert('Error posting job');
    }
}

async function createUpsell(itemId, itemType, upsellType) {
    const pricing = {
        promoted: 29.99,
        featured: 79.99,
        sponsored: 149.99,
        network_wide: 299.99
    };
    
    try {
        const response = await fetch('/api/jobs/upsells', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                upsellable_type: itemType,
                upsellable_id: itemId,
                upsell_type: upsellType,
                price: pricing[upsellType],
                currency: 'USD'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirect to payment or activate automatically
            await activateUpsell(data.data.id);
        }
    } catch (error) {
        console.error('Error creating upsell:', error);
    }
}

async function activateUpsell(upsellId) {
    try {
        const response = await fetch(`/api/jobs/upsells/${upsellId}/activate`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            }
        });
    } catch (error) {
        console.error('Error activating upsell:', error);
    }
}

function showSeekerDetails(seekerId) {
    // Implementation for showing seeker details
    console.log('Show seeker details:', seekerId);
}
</script>
@endsection
