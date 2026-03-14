@extends('layouts.app')

@section('title', 'Post a Funding Project')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-2 text-gray-900">Create Your Funding Project</h1>
            <p class="text-xl text-gray-600 mb-4">Share your vision and connect with funders worldwide</p>
            <div class="flex justify-center gap-4 text-sm text-gray-500">
                <span>✓ Premium exposure options</span>
                <span>✓ Comprehensive project tools</span>
                <span>✓ Global reach</span>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <span class="ml-2 text-sm font-medium">Project Info</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Story & Vision</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Funding Details</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">4</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Media & Verification</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">5</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Promotion</span>
                </div>
            </div>
        </div>

        <form action="{{ route('api.v1.funding.store') }}" method="POST" enctype="multipart/form-data" id="fundingForm">
            @csrf

            <!-- Step 1: Project Type & Basic Information -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6" id="step1">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span>
                    Project Type & Basic Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Project Type *</label>
                        <select name="project_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Project Type</option>
                            <option value="personal">👤 Personal Project</option>
                            <option value="startup">🚀 Startup / Business Project</option>
                            <option value="community">🤝 Community / Charity Project</option>
                            <option value="creative">💡 Creative / Innovation Project</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                        <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Category</option>
                            <option value="technology">💻 Technology</option>
                            <option value="creative_arts">🎨 Creative Arts</option>
                            <option value="community_social_impact">🌍 Community & Social Impact</option>
                            <option value="health_wellness">🏥 Health & Wellness</option>
                            <option value="education">📚 Education</option>
                            <option value="real_estate">🏠 Real Estate</option>
                            <option value="environment">🌱 Environment</option>
                            <option value="startups_business">💼 Startups & Business</option>
                            <option value="other">📌 Other</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project Title *</label>
                    <input type="text" name="title" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required maxlength="255" placeholder="Enter a catchy, descriptive title">
                    <p class="text-xs text-gray-500 mt-1">Make it memorable and clear</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Short Tagline (Max 80 characters)</label>
                    <input type="text" name="tagline" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" maxlength="80" placeholder="A powerful one-liner that captures your essence">
                    <div class="text-xs text-gray-500 mt-1">
                        <span id="taglineCount">0</span>/80 characters
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Country *</label>
                        <input type="text" name="country" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required placeholder="United States">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                        <input type="text" name="city" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="New York">
                    </div>
                </div>
            </div>

            <!-- Step 2: Project Story & Vision -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6" id="step2">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">2</span>
                    Project Story & Vision
                </h2>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Project Description *</label>
                        <textarea name="description" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="6" required placeholder="Describe your project in detail. What makes it unique? Who will benefit?"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimum 50 characters. Be detailed and compelling.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">The Problem You're Solving *</label>
                        <textarea name="problem_solving" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="5" required placeholder="What specific problem does your project address? How does it make things better?"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Your Vision & Mission *</label>
                        <textarea name="vision_mission" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="5" required placeholder="What's your long-term vision? What impact do you want to create?"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Why This Matters Now</label>
                        <textarea name="why_now" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="4" placeholder="Why is this the perfect time for your project? What's the urgency or opportunity?"></textarea>
                    </div>
                </div>
            </div>

            <!-- Team Members Section -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                <h3 class="text-xl font-bold mb-4">Team Members</h3>
                <div id="teamMembersContainer" class="space-y-4">
                    <div class="team-member-item border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="team_members[0][name]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="John Doe">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <input type="text" name="team_members[0][role]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="CEO">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Photo (Optional)</label>
                                <input type="file" name="team_members[0][photo]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addTeamMember()" class="mt-4 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                    + Add Team Member
                </button>
            </div>

            <!-- Step 3: Funding Details -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6" id="step3">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">3</span>
                    Funding Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Funding Goal *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">$</span>
                            <input type="number" name="funding_goal" class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required min="1" placeholder="10,000">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Currency *</label>
                        <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="USD">🇺🇸 USD</option>
                            <option value="GBP">🇬🇧 GBP</option>
                            <option value="EUR">🇪🇺 EUR</option>
                            <option value="AUD">🇦🇺 AUD</option>
                            <option value="CAD">🇨🇦 CAD</option>
                            <option value="INR">🇮🇳 INR</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Contribution *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">$</span>
                            <input type="number" name="minimum_contribution" class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required min="1" placeholder="5">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Funding Model *</label>
                        <select name="funding_model" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Funding Model</option>
                            <option value="donation">💝 Donation-Based</option>
                            <option value="reward">🎁 Reward-Based</option>
                            <option value="equity">📈 Equity (Future)</option>
                            <option value="loan">💰 Loan-Based (Future)</option>
                            <option value="hybrid">🔄 Hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Funding Timeline</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Start Date</label>
                                <input type="datetime-local" name="funding_starts_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">End Date (Optional)</label>
                                <input type="datetime-local" name="funding_ends_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Use of Funds Breakdown -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Use of Funds Breakdown</h3>
                    <div id="useOfFundsContainer" class="space-y-3">
                        <div class="use-of-funds-item flex gap-3 items-center">
                            <input type="text" name="use_of_funds[0][item]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Marketing">
                            <div class="relative">
                                <span class="absolute left-2 top-2 text-gray-500">$</span>
                                <input type="number" name="use_of_funds[0][amount]" class="pl-6 pr-2 py-2 w-32 border border-gray-300 rounded-lg" placeholder="0" min="0">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addUseOfFundsItem()" class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                        + Add Item
                    </button>
                </div>

                <!-- Milestones -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Timeline & Milestones</h3>
                    <div id="milestonesContainer" class="space-y-3">
                        <div class="milestone-item flex gap-3 items-center">
                            <input type="text" name="milestones[0][milestone]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Product Launch">
                            <input type="date" name="milestones[0][expected_date]" class="px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <button type="button" onclick="addMilestone()" class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                        + Add Milestone
                    </button>
                </div>
            </div>

            <!-- Step 4: Media & Verification -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6" id="step4">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">4</span>
                    Media & Verification
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cover Image *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition">
                            <input type="file" name="cover_image" class="hidden" id="coverImageInput" accept="image/*" required>
                            <label for="coverImageInput" class="cursor-pointer">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-sm">Click to upload cover image</p>
                                    <p class="text-xs text-gray-500">Max 5MB, JPEG, PNG, GIF</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Images</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition">
                            <input type="file" name="additional_images[]" class="hidden" id="additionalImagesInput" accept="image/*" multiple>
                            <label for="additionalImagesInput" class="cursor-pointer">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm">Click to upload additional images</p>
                                    <p class="text-xs text-gray-500">Max 5 images, 2MB each</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pitch Video URL</label>
                    <input type="url" name="pitch_video" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://youtu.be/your-video-id">
                    <p class="text-xs text-gray-500 mt-1">YouTube, Vimeo, or other video platform URL</p>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Supporting Documents</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition">
                        <input type="file" name="documents[]" class="hidden" id="documentsInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple>
                        <label for="documentsInput" class="cursor-pointer">
                            <div class="text-gray-400">
                                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm">Click to upload documents</p>
                                <p class="text-xs text-gray-500">Pitch deck, financials, business plan (Max 10 files, 5MB each)</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Verification Section -->
                <div class="mt-8 p-6 bg-blue-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Verification & Trust Building
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Identity Verification Document</label>
                            <div class="border border-gray-300 rounded-lg p-4">
                                <input type="file" name="identity_verification" class="w-full" accept=".pdf,.jpg,.jpeg,.png">
                                <p class="text-xs text-gray-500 mt-2">ID card, passport, or business registration</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Registration Number</label>
                            <input type="text" name="business_registration_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="If applicable">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Business Registration Document</label>
                        <div class="border border-gray-300 rounded-lg p-4">
                            <input type="file" name="business_registration_document" class="w-full" accept=".pdf,.jpg,.jpeg,.png">
                            <p class="text-xs text-gray-500 mt-2">Business registration certificate (if applicable)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Social Links -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                <h3 class="text-xl font-bold mb-4">Contact & Social Presence</h3>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project Website</label>
                    <input type="url" name="website" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://yourproject.com">
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-3">Social Media Links</h4>
                    <div id="socialLinksContainer" class="space-y-3">
                        <div class="social-link-item flex gap-3 items-center">
                            <select name="social_links[0][platform]" class="px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">Select Platform</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="instagram">Instagram</option>
                                <option value="youtube">YouTube</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="url" name="social_links[0][url]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="https://...">
                        </div>
                    </div>
                    <button type="button" onclick="addSocialLink()" class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                        + Add Social Link
                    </button>
                </div>
            </div>

            <!-- Step 5: Premium Promotion Options -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-6" id="step5">
                <h2 class="text-2xl font-bold mb-6 flex items-center">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-8 h-8 flex items-center justify-center mr-3">5</span>
                    Premium Promotion Options
                </h2>

                <div class="text-center mb-8">
                    <p class="text-gray-600 mb-4">Boost your project's visibility with our premium promotion packages</p>
                    <div class="flex justify-center gap-2 text-sm text-green-600">
                        <span>✓ Instant activation</span>
                        <span>✓ Cancel anytime</span>
                        <span>✓ Proven results</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Promoted Project -->
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-blue-400 transition cursor-pointer relative">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Promoted Project</h3>
                            <div class="text-2xl font-bold text-gray-900 mb-4">$29.99</div>
                            <ul class="text-left text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Highlighted card design
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Appears above standard listings
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    "Promoted" badge
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    2× more visibility
                                </li>
                            </ul>
                            <button type="button" onclick="selectUpsell('promoted', 29.99)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                                Select This Plan
                            </button>
                        </div>
                    </div>

                    <!-- Featured Project -->
                    <div class="border-2 border-purple-400 rounded-xl p-6 hover:border-purple-500 transition cursor-pointer relative">
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <span class="bg-purple-600 text-white px-3 py-1 rounded-full text-xs font-bold">MOST POPULAR</span>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Featured Project</h3>
                            <div class="text-2xl font-bold text-gray-900 mb-4">$59.99</div>
                            <ul class="text-left text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Top of category pages
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Larger card design
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Priority in search results
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Weekly "Top Projects" email
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    "Featured" badge
                                </li>
                            </ul>
                            <button type="button" onclick="selectUpsell('featured', 59.99)" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition">
                                Select This Plan
                            </button>
                        </div>
                    </div>

                    <!-- Sponsored Project -->
                    <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-yellow-400 transition cursor-pointer relative">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Sponsored Project</h3>
                            <div class="text-2xl font-bold text-gray-900 mb-4">$99.99</div>
                            <ul class="text-left text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Homepage placement
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Category top placement
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Homepage slider inclusion
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Social media promotion
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    "Sponsored" badge
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Maximum visibility
                                </li>
                            </ul>
                            <button type="button" onclick="selectUpsell('sponsored', 99.99)" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 rounded-lg transition">
                                Select This Plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selected Upsell Display -->
                <div id="selectedUpsell" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-semibold text-green-800">Selected Promotion Plan</h4>
                            <p id="selectedUpsellDetails" class="text-green-700"></p>
                        </div>
                        <button type="button" onclick="clearUpsell()" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="selected_upsell_type" id="selectedUpsellType">
                <input type="hidden" name="selected_upsell_price" id="selectedUpsellPrice">
            </div>

            <!-- Terms & Submit -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="space-y-4 mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms_agreed" class="mt-1 mr-3" required>
                        <span class="text-sm text-gray-700">I agree to the <a href="/terms" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="/funding-guidelines" class="text-blue-600 hover:underline">Funding Guidelines</a></span>
                    </label>
                    
                    <label class="flex items-start">
                        <input type="checkbox" name="accuracy_confirmed" class="mt-1 mr-3" required>
                        <span class="text-sm text-gray-700">I confirm that all information provided is accurate and truthful</span>
                    </label>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Project
                    </button>
                    <a href="/funding" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-4 px-6 rounded-lg text-center transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Dynamic form elements
let teamMemberCount = 1;
let useOfFundsCount = 1;
let milestoneCount = 1;
let socialLinkCount = 1;
let selectedUpsellData = null;

// Tagline character counter
document.querySelector('input[name="tagline"]').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('taglineCount').textContent = count;
    if (count > 80) {
        this.value = this.value.substring(0, 80);
        document.getElementById('taglineCount').textContent = 80;
    }
});

// Team members
function addTeamMember() {
    const container = document.getElementById('teamMembersContainer');
    const div = document.createElement('div');
    div.className = 'team-member-item border border-gray-200 rounded-lg p-4';
    div.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium">Team Member ${teamMemberCount + 1}</h4>
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="team_members[${teamMemberCount}][name]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <input type="text" name="team_members[${teamMemberCount}][role]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="CEO">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo (Optional)</label>
                <input type="file" name="team_members[${teamMemberCount}][photo]" class="w-full px-3 py-2 border border-gray-300 rounded-lg" accept="image/*">
            </div>
        </div>
    `;
    container.appendChild(div);
    teamMemberCount++;
}

// Use of funds
function addUseOfFundsItem() {
    const container = document.getElementById('useOfFundsContainer');
    const div = document.createElement('div');
    div.className = 'use-of-funds-item flex gap-3 items-center';
    div.innerHTML = `
        <input type="text" name="use_of_funds[${useOfFundsCount}][item]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Marketing">
        <div class="relative">
            <span class="absolute left-2 top-2 text-gray-500">$</span>
            <input type="number" name="use_of_funds[${useOfFundsCount}][amount]" class="pl-6 pr-2 py-2 w-32 border border-gray-300 rounded-lg" placeholder="0" min="0">
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(div);
    useOfFundsCount++;
}

// Milestones
function addMilestone() {
    const container = document.getElementById('milestonesContainer');
    const div = document.createElement('div');
    div.className = 'milestone-item flex gap-3 items-center';
    div.innerHTML = `
        <input type="text" name="milestones[${milestoneCount}][milestone]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., Product Launch">
        <input type="date" name="milestones[${milestoneCount}][expected_date]" class="px-3 py-2 border border-gray-300 rounded-lg">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(div);
    milestoneCount++;
}

// Social links
function addSocialLink() {
    const container = document.getElementById('socialLinksContainer');
    const div = document.createElement('div');
    div.className = 'social-link-item flex gap-3 items-center';
    div.innerHTML = `
        <select name="social_links[${socialLinkCount}][platform]" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Select Platform</option>
            <option value="facebook">Facebook</option>
            <option value="twitter">Twitter</option>
            <option value="linkedin">LinkedIn</option>
            <option value="instagram">Instagram</option>
            <option value="youtube">YouTube</option>
            <option value="other">Other</option>
        </select>
        <input type="url" name="social_links[${socialLinkCount}][url]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="https://...">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    container.appendChild(div);
    socialLinkCount++;
}

// Upsell selection
function selectUpsell(type, price) {
    selectedUpsellData = { type, price };
    document.getElementById('selectedUpsellType').value = type;
    document.getElementById('selectedUpsellPrice').value = price;
    
    const names = {
        'promoted': 'Promoted Project',
        'featured': 'Featured Project', 
        'sponsored': 'Sponsored Project'
    };
    
    document.getElementById('selectedUpsellDetails').textContent = `${names[type]} - $${price}`;
    document.getElementById('selectedUpsell').classList.remove('hidden');
    
    // Update button states
    document.querySelectorAll('[onclick^="selectUpsell"]').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-green-500');
    });
    event.target.classList.add('ring-2', 'ring-green-500');
}

function clearUpsell() {
    selectedUpsellData = null;
    document.getElementById('selectedUpsellType').value = '';
    document.getElementById('selectedUpsellPrice').value = '';
    document.getElementById('selectedUpsell').classList.add('hidden');
    
    document.querySelectorAll('[onclick^="selectUpsell"]').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-green-500');
    });
}

// Form submission
document.getElementById('fundingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating Project...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('/api/v1/funding', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // If upsell was selected, process payment
            if (selectedUpsellData) {
                await processUpsellPayment(data.data.id);
            } else {
                alert('Project created successfully!');
                window.location.href = '/funding/' + data.data.id;
            }
        } else {
            alert('Error: ' + (data.message || JSON.stringify(data.errors || 'Failed to create project')));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        alert('Error: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

async function processUpsellPayment(projectId) {
    try {
        const response = await fetch(`/api/v1/funding/${projectId}/upsell`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: selectedUpsellData.type,
                currency: 'USD'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Project created successfully! Your promotion package has been added to your account.');
            window.location.href = '/funding/' + projectId;
        } else {
            alert('Project created but there was an issue with the promotion: ' + (data.message || 'Please contact support'));
            window.location.href = '/funding/' + projectId;
        }
    } catch (error) {
        alert('Project created but there was an issue with the promotion: ' + error.message);
        window.location.href = '/funding/' + projectId;
    }
}

// File preview functionality
document.getElementById('coverImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'mt-3 max-h-32 rounded-lg';
            
            const label = document.querySelector('label[for="coverImageInput"]');
            label.innerHTML = '';
            label.appendChild(preview);
        };
        reader.readAsDataURL(file);
    }
});

// Progress indicator update
function updateProgress() {
    const steps = ['step1', 'step2', 'step3', 'step4', 'step5'];
    const progressElements = document.querySelectorAll('.flex.items-center.justify-between .flex.items-center');
    
    // This would need more complex logic to track form completion
    // For now, it's a placeholder for future enhancement
}
</script>
@endsection
