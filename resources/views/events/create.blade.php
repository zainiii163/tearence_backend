@extends('layouts.app')

@section('title', 'Create Event - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Event</h1>
            <p class="text-gray-600">Reach thousands of potential attendees with your event</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1-circle" class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                    <span class="ml-2 text-purple-600 font-semibold">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-purple-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step2-circle" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">2</div>
                    <span class="ml-2 text-gray-600">Details</span>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step3-circle" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">3</div>
                    <span class="ml-2 text-gray-600">Media & Promotion</span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="event-form" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <!-- Step 1: Basic Information -->
            <div id="step1" class="step-content">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Title *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter your event title">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="category" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select a category</option>
                            <option value="concert">Concerts & Music</option>
                            <option value="workshop">Workshops</option>
                            <option value="party">Parties & Nightlife</option>
                            <option value="festival">Festivals</option>
                            <option value="conference">Business Conferences</option>
                            <option value="sports">Sports Events</option>
                            <option value="cultural">Cultural Events</option>
                            <option value="food_drink">Food & Drink</option>
                            <option value="charity">Charity Events</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date & Time *</label>
                        <input type="datetime-local" name="date_time" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Venue Name</label>
                        <input type="text" name="venue_name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="If venue is already known">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                        <input type="text" name="country" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="United States">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                        <input type="text" name="city" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="New York">
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <button type="button" onclick="nextStep()" 
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Next Step
                    </button>
                </div>
            </div>

            <!-- Step 2: Event Details -->
            <div id="step2" class="step-content hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Event Details</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" required rows="6"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                  placeholder="Describe your event in detail..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Type *</label>
                            <select name="price_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    onchange="togglePriceField()">
                                <option value="free">Free</option>
                                <option value="paid">Paid</option>
                                <option value="donation">Donation</option>
                            </select>
                        </div>
                        
                        <div id="price-field">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ticket Price</label>
                            <input type="number" name="ticket_price" step="0.01" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="0.00">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expected Attendance</label>
                            <input type="number" name="expected_attendance" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="100">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age Restrictions</label>
                            <input type="text" name="age_restrictions"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="e.g., 18+, All ages">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dress Code</label>
                            <input type="text" name="dress_code"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="e.g., Formal, Casual">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ticket Booking Link</label>
                            <input type="url" name="ticket_link"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="https://...">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule/Agenda</label>
                        <textarea name="schedule" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                  placeholder="Detailed schedule of the event..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-between">
                    <button type="button" onclick="previousStep()" 
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        Previous Step
                    </button>
                    <button type="button" onclick="nextStep()" 
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Next Step
                    </button>
                </div>
            </div>

            <!-- Step 3: Media & Promotion -->
            <div id="step3" class="step-content hidden">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Media & Promotion</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                        <input type="email" name="contact_email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="contact@example.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Images</label>
                        <div id="image-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition-colors duration-200 cursor-pointer">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-gray-600">Click to upload images or drag and drop</p>
                            <p class="text-gray-500 text-sm mt-1">PNG, JPG, GIF up to 10MB</p>
                            <input type="file" id="image-input" multiple accept="image/*" class="hidden">
                        </div>
                        <div id="image-preview" class="grid grid-cols-3 gap-4 mt-4"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Video Link</label>
                        <input type="url" name="video_link"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="YouTube or Vimeo video URL">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Social Media Links</label>
                        <div id="social-links" class="space-y-2">
                            <div class="flex gap-2">
                                <input type="url" placeholder="Facebook URL" 
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <button type="button" onclick="addSocialLink()" 
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                                    Add More
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Promotion Options -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-purple-900 mb-4">Boost Your Event Visibility</h3>
                        <div class="space-y-4">
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="standard" checked class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-gray-900">Standard Listing</p>
                                    <p class="text-sm text-gray-600">Basic event listing with standard visibility</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="promoted" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-purple-600">Promoted Listing - $19.99</p>
                                    <p class="text-sm text-gray-600">Highlight your event and appear above standard listings</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="featured" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-purple-600">Featured Listing - $49.99</p>
                                    <p class="text-sm text-gray-600">Top placement in category + larger card + email inclusion</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="sponsored" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-purple-600">Sponsored Listing - $99.99</p>
                                    <p class="text-sm text-gray-600">Homepage placement + social media promotion</p>
                                </div>
                            </label>
                            
                            <label class="flex items-start cursor-pointer">
                                <input type="radio" name="promotion_tier" value="spotlight" class="mt-1">
                                <div class="ml-3">
                                    <p class="font-semibold text-purple-600">Spotlight Listing - $199.99</p>
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
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Create Event
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

function nextStep() {
    if (validateStep(currentStep)) {
        document.getElementById(`step${currentStep}`).classList.add('hidden');
        document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold';
        currentStep++;
        document.getElementById(`step${currentStep}`).classList.remove('hidden');
        document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-semibold';
    }
}

function previousStep() {
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold';
    currentStep--;
    document.getElementById(`step${currentStep}`).classList.remove('hidden');
    document.getElementById(`step${currentStep}-circle`).className = 'w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center font-semibold';
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

function togglePriceField() {
    const priceType = document.querySelector('select[name="price_type"]').value;
    const priceField = document.getElementById('price-field');
    
    if (priceType === 'paid') {
        priceField.classList.remove('hidden');
        document.querySelector('input[name="ticket_price"]').required = true;
    } else {
        priceField.classList.add('hidden');
        document.querySelector('input[name="ticket_price"]').required = false;
        document.querySelector('input[name="ticket_price"]').value = '';
    }
}

function addSocialLink() {
    const container = document.getElementById('social-links');
    const newLink = document.createElement('div');
    newLink.className = 'flex gap-2';
    newLink.innerHTML = `
        <input type="url" placeholder="Social media URL" 
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
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

// Form submission
document.getElementById('event-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Add uploaded images
    if (uploadedImages.length > 0) {
        data.images = uploadedImages;
    }
    
    // Collect social links
    const socialLinks = Array.from(document.querySelectorAll('#social-links input[type="url"]'))
        .map(input => input.value)
        .filter(url => url.trim());
    
    if (socialLinks.length > 0) {
        data.social_links = socialLinks;
    }
    
    try {
        const response = await fetch('/api/v1/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Event created successfully!');
            window.location.href = `/events/${result.data.slug}`;
        } else {
            alert('Error creating event: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating event. Please try again.');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    togglePriceField();
});
</script>
@endsection
