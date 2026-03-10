<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Travel Advert - WorldwideAdverts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: white;
        }
        .step-indicator.completed {
            background: #10b981;
            color: white;
        }
        .form-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .form-section.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .promotion-card {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }
        .promotion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .promotion-card.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .world-map {
            height: 400px;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .rich-editor {
            min-height: 200px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.75rem;
        }
        .sticky-summary {
            position: sticky;
            top: 100px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-globe text-blue-600 text-2xl mr-2"></i>
                        <span class="font-bold text-xl text-gray-900">WorldwideAdverts</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                        <a href="/categories" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Categories</a>
                        <a href="/vehicles" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Vehicles</a>
                        <a href="/books" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Books & Literature</a>
                        <a href="/resorts-travel" class="text-blue-600 border-b-2 border-blue-600 px-3 py-2 rounded-md text-sm font-medium">Resorts & Travel</a>
                        <a href="/events" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Events</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/resorts-travel" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Travel
                    </a>
                    <a href="/login" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Login</a>
                    <a href="/register" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Post Your Travel Advert</h1>
                        <p class="text-gray-600">Reach millions of travelers worldwide with your travel services</p>
                    </div>

                    <!-- Step Indicators -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between overflow-x-auto">
                            <div class="flex items-center min-w-max">
                                <div id="step1Indicator" class="step-indicator active w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm">1</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step2Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">2</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step3Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">3</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step4Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">4</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step5Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">5</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step6Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">6</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step7Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">7</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step8Indicator" class="step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm">8</div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-4 text-xs text-gray-600 overflow-x-auto">
                            <span class="min-w-fit">Advert Type</span>
                            <span class="min-w-fit">Basic Info</span>
                            <span class="min-w-fit">Details</span>
                            <span class="min-w-fit">Description</span>
                            <span class="min-w-fit">Contact</span>
                            <span class="min-w-fit">Location</span>
                            <span class="min-w-fit">Promotion</span>
                            <span class="min-w-fit">Submit</span>
                        </div>
                    </div>

                    <form id="travelAdvertForm">
                        <!-- Step 1: Select Advert Type -->
                        <div id="step1" class="form-section active">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Select Advert Type</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div onclick="selectAdvertType('accommodation')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card" data-type="accommodation">
                                    <div class="text-5xl mb-4 text-center">🏨</div>
                                    <h3 class="text-xl font-semibold mb-2 text-center">Accommodation</h3>
                                    <p class="text-gray-600 mb-4 text-center">Hotels, resorts, B&Bs, holiday homes and more</p>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Resort</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Hotel</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>B&B</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Guest House</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Holiday Home</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Villa</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Lodge</div>
                                    </div>
                                </div>
                                
                                <div onclick="selectAdvertType('transport')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card" data-type="transport">
                                    <div class="text-5xl mb-4 text-center">🚗</div>
                                    <h3 class="text-xl font-semibold mb-2 text-center">Transport Services</h3>
                                    <p class="text-gray-600 mb-4 text-center">Airport transfers, car hire, shuttles and more</p>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Airport Transfer</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Taxi / Chauffeur</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Car Hire</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Shuttle Bus</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Tour Bus</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Boat / Ferry</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Motorbike Rental</div>
                                    </div>
                                </div>
                                
                                <div onclick="selectAdvertType('experience')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card" data-type="experience">
                                    <div class="text-5xl mb-4 text-center">🎯</div>
                                    <h3 class="text-xl font-semibold mb-2 text-center">Travel Experiences</h3>
                                    <p class="text-gray-600 mb-4 text-center">Tours, excursions, adventures and wellness</p>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Tours</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Excursions</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Adventure Packages</div>
                                        <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Wellness Retreats</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Basic Advert Information -->
                        <div id="step2" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Basic Advert Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Advert Title *</label>
                                    <input type="text" id="advertTitle" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your advert title">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                                    <input type="text" id="advertTagline" name="tagline" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="A catchy tagline">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                    <select id="advertCategory" name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select a category</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                                    <input type="text" id="advertCountry" name="country" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Country">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City / Region *</label>
                                    <input type="text" id="advertCity" name="city" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="City or region">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Price per night / trip *</label>
                                    <input type="number" id="advertPrice" name="price" required step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Availability Dates</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">From</label>
                                            <input type="date" id="availabilityStart" name="availability_start" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">To</label>
                                            <input type="date" id="availabilityEnd" name="availability_end" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Media Uploads</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Main Image *</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition" onclick="document.getElementById('mainImage').click()">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-600">Click to upload main image</p>
                                            <p class="text-xs text-gray-500">JPG, PNG up to 2MB</p>
                                            <input type="file" id="mainImage" name="main_image" accept="image/*" class="hidden" onchange="previewMainImage(event)">
                                            <div id="mainImagePreview" class="mt-4"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images (max 10)</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition" onclick="document.getElementById('additionalImages').click()">
                                            <i class="fas fa-images text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-600">Click to upload additional images</p>
                                            <p class="text-xs text-gray-500">JPG, PNG up to 2MB each</p>
                                            <input type="file" id="additionalImages" name="additional_images" accept="image/*" multiple class="hidden" onchange="previewAdditionalImages(event)">
                                            <div id="additionalImagesPreview" class="mt-4 grid grid-cols-3 gap-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Video Link (optional)</label>
                                    <input type="url" id="videoLink" name="video_link" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://youtube.com/watch?v=...">
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Dynamic Details -->
                        <div id="step3" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Detailed Information</h2>
                            
                            <!-- Accommodation Details -->
                            <div id="accommodationDetails" class="space-y-6 hidden">
                                <h3 class="text-lg font-medium text-gray-900">Accommodation Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Room Types</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="room_types[]" value="single" class="mr-2">
                                                <span class="text-sm">Single Room</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="room_types[]" value="double" class="mr-2">
                                                <span class="text-sm">Double Room</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="room_types[]" value="suite" class="mr-2">
                                                <span class="text-sm">Suite</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="room_types[]" value="family" class="mr-2">
                                                <span class="text-sm">Family Room</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                                        <div class="space-y-2 max-h-48 overflow-y-auto">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="wi_fi" class="mr-2">
                                                <span class="text-sm">Wi-Fi</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="pool" class="mr-2">
                                                <span class="text-sm">Swimming Pool</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="parking" class="mr-2">
                                                <span class="text-sm">Parking</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="breakfast" class="mr-2">
                                                <span class="text-sm">Breakfast Included</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="air_conditioning" class="mr-2">
                                                <span class="text-sm">Air Conditioning</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="amenities[]" value="pet_friendly" class="mr-2">
                                                <span class="text-sm">Pet Friendly</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Distance to City Centre (km)</label>
                                        <input type="number" name="distance_to_city_centre" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Guest Capacity</label>
                                        <input type="number" name="guest_capacity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Time</label>
                                        <input type="time" name="check_in_time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Time</label>
                                        <input type="time" name="check_out_time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Transport Details -->
                            <div id="transportDetails" class="space-y-6 hidden">
                                <h3 class="text-lg font-medium text-gray-900">Transport Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                                        <input type="text" name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Sedan, SUV, Van">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Passenger Capacity</label>
                                        <input type="number" name="passenger_capacity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Luggage Capacity</label>
                                        <input type="number" name="luggage_capacity" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Area</label>
                                        <input type="text" name="service_area" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., London City, All Airports">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Operating Hours</label>
                                        <div class="grid grid-cols-7 gap-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="monday" class="mr-1">
                                                <span class="text-xs">Mon</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="tuesday" class="mr-1">
                                                <span class="text-xs">Tue</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="wednesday" class="mr-1">
                                                <span class="text-xs">Wed</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="thursday" class="mr-1">
                                                <span class="text-xs">Thu</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="friday" class="mr-1">
                                                <span class="text-xs">Fri</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="saturday" class="mr-1">
                                                <span class="text-xs">Sat</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="operating_hours[]" value="sunday" class="mr-1">
                                                <span class="text-xs">Sun</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="airport_pickup" class="mr-2">
                                            <span class="text-sm">Airport Pickup Available</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Experience Details -->
                            <div id="experienceDetails" class="space-y-6 hidden">
                                <h3 class="text-lg font-medium text-gray-900">Experience Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                                        <input type="text" name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 2 hours, Full day, 3 days">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Group Size</label>
                                        <input type="number" name="group_size" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">What's Included</label>
                                        <textarea name="whats_included" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="List all items and services included in the experience"></textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">What to Bring</label>
                                        <textarea name="what_to_bring" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="List items participants should bring"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Description -->
                        <div id="step4" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Description</h2>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Overview *</label>
                                    <textarea name="overview" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief overview of your travel service"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Key Features</label>
                                    <textarea name="key_features" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="List the key features and highlights"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Why Travellers Love This</label>
                                    <textarea name="why_travellers_love_this" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="What makes your service special?"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nearby Attractions</label>
                                    <textarea name="nearby_attractions" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Popular attractions and points of interest nearby"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                                    <textarea name="additional_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Any additional information travellers should know"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Poster Information -->
                        <div id="step5" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Contact Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name *</label>
                                    <input type="text" name="contact_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Name</label>
                                    <input type="text" name="business_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                                    <input type="tel" name="phone_number" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                    <input type="url" name="website" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-500 transition" onclick="document.getElementById('businessLogo').click()">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-600">Upload logo</p>
                                        <input type="file" id="businessLogo" name="logo" accept="image/*" class="hidden" onchange="previewLogo(event)">
                                        <div id="logoPreview" class="mt-2"></div>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="verified_business" class="mr-2">
                                        <span class="text-sm">Get Verified Business Badge (+£10)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Location Map -->
                        <div id="step6" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Location</h2>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Full address"></textarea>
                                </div>
                                
                                <div>
                                    <label class="flex items-center mb-4">
                                        <input type="checkbox" id="approximateLocation" name="is_approximate_location" class="mr-2">
                                        <span class="text-sm">Show approximate location only</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Drop a pin on the map to set your location</label>
                                    <div id="locationMap" class="world-map"></div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                                        <input type="number" id="latitude" name="latitude" step="0.000001" min="-90" max="90" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                                        <input type="number" id="longitude" name="longitude" step="0.000001" min="-180" max="180" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7: Premium Upsale Options -->
                        <div id="step7" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Choose Your Promotion Package</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div onclick="selectPromotion('standard')" class="promotion-card rounded-lg p-6 cursor-pointer" data-tier="standard">
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-list text-gray-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold">Standard</h3>
                                            <p class="text-sm text-gray-600">Basic listing</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 mb-4">FREE</div>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Basic listing</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Standard placement</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Contact information</li>
                                    </ul>
                                </div>

                                <div onclick="selectPromotion('promoted')" class="promotion-card rounded-lg p-6 cursor-pointer" data-tier="promoted">
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-star text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold">Promoted</h3>
                                            <p class="text-sm text-gray-600">Enhanced visibility</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 mb-4">£29.99<span class="text-sm text-gray-600">/30 days</span></div>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Highlighted listing</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Appears above standard ads</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Promoted badge</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>2× more visibility</li>
                                    </ul>
                                </div>

                                <div onclick="selectPromotion('featured')" class="promotion-card rounded-lg p-6 cursor-pointer border-2 border-yellow-400" data-tier="featured">
                                    <div class="absolute -mt-2 -mr-2">
                                        <span class="bg-yellow-400 text-yellow-900 text-xs px-2 py-1 rounded-full font-semibold">MOST POPULAR</span>
                                    </div>
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-crown text-purple-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold">Featured</h3>
                                            <p class="text-sm text-gray-600">Premium placement</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 mb-4">£59.99<span class="text-sm text-gray-600">/30 days</span></div>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Top of category pages</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Larger advert card</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Priority in search results</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Included in weekly email</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Featured badge</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>4× more visibility</li>
                                    </ul>
                                </div>

                                <div onclick="selectPromotion('sponsored')" class="promotion-card rounded-lg p-6 cursor-pointer" data-tier="sponsored">
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                            <i class="fas fa-rocket text-orange-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold">Sponsored</h3>
                                            <p class="text-sm text-gray-600">Ultimate visibility</p>
                                        </div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 mb-4">£99.99<span class="text-sm text-gray-600">/30 days</span></div>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Homepage placement</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Category top placement</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Included in homepage slider</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Social media promotion</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Sponsored badge</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Maximum visibility</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Comparison Table -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4">Compare Features</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left p-2">Feature</th>
                                                <th class="text-center p-2">Standard</th>
                                                <th class="text-center p-2">Promoted</th>
                                                <th class="text-center p-2">Featured</th>
                                                <th class="text-center p-2">Sponsored</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="border-b">
                                                <td class="p-2">Basic Listing</td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                            </tr>
                                            <tr class="border-b">
                                                <td class="p-2">Highlighted</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                            </tr>
                                            <tr class="border-b">
                                                <td class="p-2">Homepage Placement</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                            </tr>
                                            <tr class="border-b">
                                                <td class="p-2">Social Media Promotion</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2">-</td>
                                                <td class="text-center p-2"><i class="fas fa-check text-green-500"></i></td>
                                            </tr>
                                            <tr>
                                                <td class="p-2">Visibility Boost</td>
                                                <td class="text-center p-2">1x</td>
                                                <td class="text-center p-2">2x</td>
                                                <td class="text-center p-2">4x</td>
                                                <td class="text-center p-2">10x</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Step 8: Final Submission -->
                        <div id="step8" class="form-section">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Review & Submit</h2>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-blue-900 mb-4">Review Your Advert</h3>
                                <div id="reviewSummary" class="space-y-4">
                                    <!-- Summary will be populated dynamically -->
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="accuracyCheckbox" required class="mr-2">
                                    <span class="text-sm text-gray-700">I confirm this advert is accurate and truthful</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="termsCheckbox" required class="mr-2">
                                    <span class="text-sm text-gray-700">I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a></span>
                                </label>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="previousStep()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition" id="prevBtn">
                                <i class="fas fa-arrow-left mr-2"></i>Previous
                            </button>
                            <button type="button" onclick="nextStep()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition" id="nextBtn">
                                Next<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition" id="submitBtn">
                                <i class="fas fa-check mr-2"></i>Submit Travel Advert
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sticky Summary Box -->
            <div class="lg:col-span-1">
                <div class="sticky-summary bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Advert Summary</h3>
                    <div id="summaryContent" class="space-y-4">
                        <div class="text-gray-500 text-sm">
                            <p>Complete the form to see your advert summary</p>
                        </div>
                    </div>
                    
                    <div id="priceSummary" class="mt-6 pt-6 border-t border-gray-200 hidden">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600">Total Cost:</span>
                            <span id="totalPrice" class="text-2xl font-bold text-blue-600">£0.00</span>
                        </div>
                        <button type="button" onclick="proceedToPayment()" class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition">
                            Proceed to Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentStep = 1;
        let selectedAdvertType = '';
        let selectedPromotion = 'standard';
        let locationMap = null;
        let marker = null;
        let formData = {};

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeLocationMap();
            loadCategories();
            updateStepIndicators();
            updateNavigationButtons();
        });

        // Step navigation
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show current step
            document.getElementById('step' + step).classList.add('active');
            
            currentStep = step;
            updateStepIndicators();
            updateNavigationButtons();
            updateSummary();
        }

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < 8) {
                    saveStepData();
                    showStep(currentStep + 1);
                }
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }

        function updateStepIndicators() {
            for (let i = 1; i <= 8; i++) {
                const indicator = document.getElementById('step' + i + 'Indicator');
                if (i < currentStep) {
                    indicator.className = 'step-indicator completed w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm';
                    indicator.innerHTML = '✓';
                } else if (i === currentStep) {
                    indicator.className = 'step-indicator active w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm';
                    indicator.innerHTML = i;
                } else {
                    indicator.className = 'step-indicator w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold text-sm';
                    indicator.innerHTML = i;
                }
            }
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');
            
            prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';
            nextBtn.style.display = currentStep === 8 ? 'none' : 'inline-flex';
            submitBtn.style.display = currentStep === 8 ? 'inline-flex' : 'none';
        }

        // Advert type selection
        function selectAdvertType(type) {
            selectedAdvertType = type;
            
            // Update UI
            document.querySelectorAll('.advert-type-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-300');
            });
            
            document.querySelector(`[data-type="${type}"]`).classList.remove('border-gray-300');
            document.querySelector(`[data-type="${type}"]`).classList.add('border-blue-500', 'bg-blue-50');
            
            // Show relevant details section in step 3
            document.getElementById('accommodationDetails').classList.add('hidden');
            document.getElementById('transportDetails').classList.add('hidden');
            document.getElementById('experienceDetails').classList.add('hidden');
            
            if (type === 'accommodation') {
                document.getElementById('accommodationDetails').classList.remove('hidden');
            } else if (type === 'transport') {
                document.getElementById('transportDetails').classList.remove('hidden');
            } else if (type === 'experience') {
                document.getElementById('experienceDetails').classList.remove('hidden');
            }
            
            updateSummary();
        }

        // Promotion selection
        function selectPromotion(tier) {
            selectedPromotion = tier;
            
            // Update UI
            document.querySelectorAll('.promotion-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            document.querySelector(`[data-tier="${tier}"]`).classList.add('selected');
            
            updateSummary();
            updatePriceSummary();
        }

        // Location map
        function initializeLocationMap() {
            locationMap = L.map('locationMap').setView([51.5074, -0.1278], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(locationMap);
            
            locationMap.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });
        }

        function setMarker(lat, lng) {
            if (marker) {
                locationMap.removeLayer(marker);
            }
            
            marker = L.marker([lat, lng]).addTo(locationMap);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }

        // Image previews
        function previewMainImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('mainImagePreview').innerHTML = 
                        `<img src="${e.target.result}" class="image-preview">`;
                };
                reader.readAsDataURL(file);
            }
        }

        function previewAdditionalImages(event) {
            const files = event.target.files;
            const preview = document.getElementById('additionalImagesPreview');
            preview.innerHTML = '';
            
            for (let i = 0; i < Math.min(files.length, 10); i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(files[i]);
            }
        }

        function previewLogo(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').innerHTML = 
                        `<img src="${e.target.result}" class="image-preview">`;
                };
                reader.readAsDataURL(file);
            }
        }

        // Form validation
        function validateCurrentStep() {
            let isValid = true;
            
            if (currentStep === 1) {
                if (!selectedAdvertType) {
                    alert('Please select an advert type');
                    isValid = false;
                }
            } else if (currentStep === 2) {
                const title = document.getElementById('advertTitle').value;
                const country = document.getElementById('advertCountry').value;
                const city = document.getElementById('advertCity').value;
                const price = document.getElementById('advertPrice').value;
                
                if (!title || !country || !city || !price) {
                    alert('Please fill in all required fields');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        // Save step data
        function saveStepData() {
            const form = document.getElementById('travelAdvertForm');
            const formData = new FormData(form);
            
            // Store form data
            for (let [key, value] of formData.entries()) {
                if (value) {
                    if (key.includes('[]')) {
                        const baseKey = key.replace('[]', '');
                        if (!formData[baseKey]) formData[baseKey] = [];
                        formData[baseKey].push(value);
                    } else {
                        formData[key] = value;
                    }
                }
            }
            
            formData.advert_type = selectedAdvertType;
            formData.promotion_tier = selectedPromotion;
        }

        // Update summary
        function updateSummary() {
            const summaryContent = document.getElementById('summaryContent');
            let html = '';
            
            if (selectedAdvertType) {
                html += `
                    <div>
                        <span class="text-sm text-gray-500">Advert Type:</span>
                        <p class="font-medium">${selectedAdvertType.charAt(0).toUpperCase() + selectedAdvertType.slice(1)}</p>
                    </div>
                `;
            }
            
            const title = document.getElementById('advertTitle').value;
            if (title) {
                html += `
                    <div>
                        <span class="text-sm text-gray-500">Title:</span>
                        <p class="font-medium">${title}</p>
                    </div>
                `;
            }
            
            const price = document.getElementById('advertPrice').value;
            if (price) {
                html += `
                    <div>
                        <span class="text-sm text-gray-500">Price:</span>
                        <p class="font-medium">£${price}</p>
                    </div>
                `;
            }
            
            if (selectedPromotion !== 'standard') {
                html += `
                    <div>
                        <span class="text-sm text-gray-500">Promotion:</span>
                        <p class="font-medium">${selectedPromotion.charAt(0).toUpperCase() + selectedPromotion.slice(1)}</p>
                    </div>
                `;
            }
            
            if (!html) {
                html = '<div class="text-gray-500 text-sm"><p>Complete the form to see your advert summary</p></div>';
            }
            
            summaryContent.innerHTML = html;
        }

        // Update price summary
        function updatePriceSummary() {
            const prices = {
                'standard': 0,
                'promoted': 29.99,
                'featured': 59.99,
                'sponsored': 99.99
            };
            
            const price = prices[selectedPromotion];
            document.getElementById('totalPrice').textContent = `£${price.toFixed(2)}`;
            
            const priceSummary = document.getElementById('priceSummary');
            if (price > 0) {
                priceSummary.classList.remove('hidden');
            } else {
                priceSummary.classList.add('hidden');
            }
        }

        // Load categories
        async function loadCategories() {
            try {
                const response = await fetch('/api/resorts-travel/categories');
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('advertCategory');
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        // Form submission
        document.getElementById('travelAdvertForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!document.getElementById('accuracyCheckbox').checked || !document.getElementById('termsCheckbox').checked) {
                alert('Please confirm the accuracy checkbox and agree to the terms');
                return;
            }
            
            // Here you would normally submit the form to your backend
            alert('Travel advert submitted successfully! This would normally save to your database.');
            
            // Redirect to travel page
            window.location.href = '/resorts-travel';
        });

        // Proceed to payment
        function proceedToPayment() {
            alert('This would normally redirect to your payment gateway');
        }
    </script>
</body>
</html>
