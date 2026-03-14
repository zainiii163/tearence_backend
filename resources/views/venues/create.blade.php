@extends('layouts.app')

@section('title', 'Create Venue - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Venue Listing</h1>
            <p class="text-gray-600">Showcase your venue to thousands of event organizers</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1-circle" class="w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                    <span class="ml-2 text-indigo-600 font-semibold">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-indigo-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step2-circle" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">2</div>
                    <span class="ml-2 text-gray-600">Details & Features</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step3-circle" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">3</div>
                    <span class="ml-2 text-gray-600">Media & Promotion</span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="venue-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <!-- Step 1: Basic Information -->
            <div id="step1" class="step-content">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Venue Name *</label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Enter your venue name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Venue Type *</label>
                        <select name="venue_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select a venue type</option>
                            <option value="wedding_hall">Wedding Hall</option>
                            <option value="conference_centre">Conference Centre</option>
                            <option value="party_hall">Party Hall</option>
                            <option value="outdoor_space">Outdoor Space</option>
                            <option value="hotel_banquet">Hotel & Banquet Room</option>
                            <option value="bar_restaurant">Bar & Restaurant</option>
                            <option value="meeting_room">Meeting Room</option>
                            <option value="exhibition_space">Exhibition Space</option>
                            <option value="sports_venue">Sports Venue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
                        <input type="number" name="capacity" required min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Maximum number of guests">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                        <input type="text" name="country" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="United States">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                        <input type="text" name="city" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="New York">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                        <input type="email" name="contact_email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="contact@venue.com">
                    </div>
                </div>
                
                <!-- Pricing -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Price</label>
                            <input type="number" name="min_price" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Starting from $0">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Price</label>
                            <input type="number" name="max_price" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Up to $0">
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button type="button" onclick="nextStep()" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        Next Step
                    </button>
                </div>
            </div>

            <!-- Step 2: Details & Features -->
            <div id="step2" class="step-content hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Venue Details & Features</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" required rows="6"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="Describe your venue in detail..."></textarea>
                    </div>
                    
                    <!-- Venue Features -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Venue Features</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="indoor" checked class="mr-2">
                                <span>Indoor Space</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="outdoor" class="mr-2">
                                <span>Outdoor Space</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="catering_available" class="mr-2">
                                <span>Catering Available</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="parking_available" class="mr-2">
                                <span>Parking Available</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="accessibility" class="mr-2">
                                <span>Wheelchair Accessible</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Amenities -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Amenities</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="wi_fi" class="mr-2">
                                <span>Wi-Fi</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="parking" class="mr-2">
                                <span>Parking</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="catering" class="mr-2">
                                <span>Catering</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="av_equipment" class="mr-2">
                                <span>AV Equipment</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="air_conditioning" class="mr-2">
                                <span>Air Conditioning</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="heating" class="mr-2">
                                <span>Heating</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="sound_system" class="mr-2">
                                <span>Sound System</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="lighting" class="mr-2">
                                <span>Lighting</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="stage" class="mr-2">
                                <span>Stage</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="dance_floor" class="mr-2">
                                <span>Dance Floor</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="bar" class="mr-2">
                                <span>Bar</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="kitchen" class="mr-2">
                                <span>Kitchen</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="restrooms" class="mr-2">
                                <span>Restrooms</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="wheelchair_access" class="mr-2">
                                <span>Wheelchair Access</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="elevator" class="mr-2">
                                <span>Elevator</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="amenities[]" value="security" class="mr-2">
                                <span>Security</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Opening Hours -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Opening Hours</label>
                        <textarea name="opening_hours" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="e.g., Mon-Fri: 9AM-9PM, Sat-Sun: 10AM-11PM"></textarea>
                    </div>
                    
                    <!-- Booking Link -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Booking Link</label>
                        <input type="url" name="booking_link"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="https://yourbookingplatform.com/venue">
                    </div>
                </div>
                
                <div class="mt-8 flex justify-between">
                    <button type="button" onclick="previousStep()" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Previous Step
                    </button>
                    <button type="button" onclick="nextStep()" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        Next Step
                    </button>
                </div>
            </div>

            <!-- Step 3: Media & Promotion -->
            <div id="step3" class="step-content hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Media & Promotion</h2>
                
                <div class="space-y-6">
                    <!-- Venue Images -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Venue Images</label>
                        <div id="image-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors duration-200 cursor-pointer">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-gray-600">Click to upload venue images or drag and drop</p>
                            <p class="text-gray-500 text-sm mt-1">PNG, JPG, GIF up to 10MB</p>
                            <input type="file" id="image-input" multiple accept="image/*" class="hidden">
                        </div>
                        <div id="image-preview" class="grid grid-cols-3 gap-4 mt-4"></div>
                    </div>
                    
                    <!-- Floor Plan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Floor Plan</label>
                        <div id="floor-plan-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors duration-200 cursor-pointer">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-600">Click to upload floor plan</p>
                            <p class="text-gray-500 text-sm mt-1">PDF, PNG, JPG up to 10MB</p>
                            <input type="file" id="floor-plan-input" accept="image/*,.pdf" class="hidden">
                        </div>
                        <div id="floor-plan-preview" class="mt-4 hidden">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <span id="floor-plan-name" class="text-gray-700"></span>
                                <button type="button" onclick="removeFloorPlan()" class="text-red-600 hover:text-red-700">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Tour -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Video Tour Link</label>
                        <input type="url" name="video_link"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="YouTube or Vimeo video URL">
                    </div>
                    
                    <!-- Social Media Links -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Social Media Links</label>
                        <div id="social-links" class="space-y-2">
                            <div class="flex gap-2">
                                <input type="url" placeholder="Facebook URL" 
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="button" onclick="addSocialLink()" 
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                                    Add More
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Promotion Options -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-indigo-900 mb-4">Boost Your Venue Visibility</h3>
                        <div class="space-y-4">
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="standard" checked class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">Standard Listing</p>
                                    <p class="text-sm text-gray-600">Basic venue listing with standard visibility</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="promoted" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-indigo-600">Promoted Listing - $29.99</p>
                                    <p class="text-sm text-gray-600">Highlight your venue and appear above standard listings</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="featured" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-indigo-600">Featured Listing - $79.99</p>
                                    <p class="text-sm text-gray-600">Top placement in category + larger card + email inclusion</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="sponsored" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-indigo-600">Sponsored Listing - $149.99</p>
                                    <p class="text-sm text-gray-600">Homepage placement + social media promotion</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="spotlight" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-indigo-600">Spotlight Listing - $299.99</p>
                                    <p class="text-sm text-gray-600">All premium features + category top placement</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-between">
                    <button type="button" onclick="previousStep()" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Previous Step
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        Create Venue
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentStep = 1;
let uploadedImages = [];
let uploadedFloorPlan = null;

function nextStep() {
    if (validateStep(currentStep)) {
        document.getElementById(`step${currentStep}`).classList.add('hidden');
        document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold';
        currentStep++;
        document.getElementById(`step${currentStep}`).classList.remove('hidden');
        document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-semibold';
    }
}

function previousStep() {
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold';
    currentStep--;
    document.getElementById(`step${currentStep}`).classList.remove('hidden');
    document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-semibold';
}

function validateStep(step) {
    const stepElement = document.getElementById(`step${step}`);
    const requiredFields = stepElement.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            alert('Please fill in all required fields');
            return false;
        }
    }
    
    return true;
}

function addSocialLink() {
    const container = document.getElementById('social-links');
    const newLink = document.createElement('div');
    newLink.className = 'flex gap-2';
    newLink.innerHTML = `
        <input type="url" placeholder="Social media URL" 
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="button" onclick="this.parentElement.remove()" 
                class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors duration-200">
            Remove
        </button>
    `;
    container.appendChild(newLink);
}

// Image upload functionality
document.getElementById('image-upload-area').addEventListener('click', function() {
    document.getElementById('image-input').click();
});

document.getElementById('image-input').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('image-preview');
    
    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                uploadedImages.push(e.target.result);
                
                const imageContainer = document.createElement('div');
                imageContainer.className = 'relative group';
                imageContainer.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                    <button type="button" onclick="removeImage(${uploadedImages.length - 1})" 
                            class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                preview.appendChild(imageContainer);
            };
            reader.readAsDataURL(file);
        }
    });
});

function removeImage(index) {
    uploadedImages.splice(index, 1);
    document.getElementById('image-preview').children[index].remove();
}

// Floor plan upload
document.getElementById('floor-plan-upload-area').addEventListener('click', function() {
    document.getElementById('floor-plan-input').click();
});

document.getElementById('floor-plan-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        uploadedFloorPlan = file.name;
        document.getElementById('floor-plan-name').textContent = file.name;
        document.getElementById('floor-plan-preview').classList.remove('hidden');
    }
});

function removeFloorPlan() {
    uploadedFloorPlan = null;
    document.getElementById('floor-plan-input').value = '';
    document.getElementById('floor-plan-preview').classList.add('hidden');
}

// Form submission
document.getElementById('venue-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Handle amenities
    const amenities = formData.getAll('amenities[]');
    if (amenities.length > 0) {
        data.amenities = amenities;
    }
    
    // Handle boolean fields
    data.indoor = formData.has('indoor');
    data.outdoor = formData.has('outdoor');
    data.catering_available = formData.has('catering_available');
    data.parking_available = formData.has('parking_available');
    data.accessibility = formData.has('accessibility');
    
    // Add uploaded images
    if (uploadedImages.length > 0) {
        data.images = uploadedImages;
    }
    
    // Add floor plan
    if (uploadedFloorPlan) {
        data.floor_plan = uploadedFloorPlan;
    }
    
    // Collect social links
    const socialLinks = Array.from(document.querySelectorAll('#social-links input[type="url"]'))
        .map(input => input.value)
        .filter(url => url.trim());
    
    if (socialLinks.length > 0) {
        data.social_links = socialLinks;
    }
    
    try {
        const response = await fetch('/api/v1/venues', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Venue created successfully!');
            window.location.href = `/venues/${result.data.slug}`;
        } else {
            alert('Error creating venue: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating venue. Please try again.');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default boolean values
    document.querySelector('input[name="indoor"]').checked = true;
});
</script>
@endsection
