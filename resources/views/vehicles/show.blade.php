<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vehicle->title }} - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .badge-promoted {
            background: linear-gradient(45deg, #10b981, #34d399);
        }
        .badge-featured {
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
        }
        .badge-sponsored {
            background: linear-gradient(45deg, #ef4444, #f87171);
        }
        .badge-top-category {
            background: linear-gradient(45deg, #8b5cf6, #a78bfa);
        }
        .image-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 8px;
            height: 400px;
        }
        .main-image {
            grid-row: span 2;
        }
        .thumbnail {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .thumbnail:hover {
            opacity: 0.8;
        }
        .thumbnail.active {
            border: 3px solid #4f46e5;
        }
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .contact-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-indigo-600">WWA</a>
                    <span class="ml-2 text-gray-600">Vehicle Details</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/vehicles" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Vehicles
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="flex items-center text-sm text-gray-600 mb-6">
            <a href="/vehicles" class="hover:text-indigo-600">Vehicles</a>
            <i class="fas fa-chevron-right mx-2"></i>
            <a href="/vehicles?category={{ $vehicle->category_id }}" class="hover:text-indigo-600">{{ $vehicle->category->name }}</a>
            <i class="fas fa-chevron-right mx-2"></i>
            <span class="text-gray-900">{{ $vehicle->title }}</span>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Images and Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Upgrade Badges -->
                @if($vehicle->is_promoted || $vehicle->is_featured || $vehicle->is_sponsored || $vehicle->is_top_of_category)
                <div class="flex space-x-2">
                    @if($vehicle->is_top_of_category)
                        <span class="badge-top-category text-sm px-3 py-1 text-white rounded-full">Top of Category</span>
                    @elseif($vehicle->is_sponsored)
                        <span class="badge-sponsored text-sm px-3 py-1 text-white rounded-full">Sponsored</span>
                    @elseif($vehicle->is_featured)
                        <span class="badge-featured text-sm px-3 py-1 text-white rounded-full">Featured</span>
                    @elseif($vehicle->is_promoted)
                        <span class="badge-promoted text-sm px-3 py-1 text-white rounded-full">Promoted</span>
                    @endif
                </div>
                @endif

                <!-- Image Gallery -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="image-gallery">
                        <div class="main-image">
                            <img id="mainImage" src="{{ $vehicle->main_image_url }}" alt="{{ $vehicle->title }}" class="w-full h-full object-cover rounded-lg">
                        </div>
                        @if($vehicle->additional_images_urls)
                            @foreach(array_slice($vehicle->additional_images_urls, 0, 2) as $image)
                                <div class="thumbnail rounded-lg overflow-hidden" onclick="changeMainImage('{{ $image }}')">
                                    <img src="{{ $image }}" alt="{{ $vehicle->title }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        @endif
                    </div>
                    @if($vehicle->video_link)
                        <div class="mt-4">
                            <a href="{{ $vehicle->video_link }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                <i class="fab fa-youtube mr-2"></i> Watch Video
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Vehicle Title and Price -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $vehicle->title }}</h1>
                            @if($vehicle->tagline)
                                <p class="text-lg text-gray-600 mb-4">{{ $vehicle->tagline }}</p>
                            @endif
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span><i class="fas fa-eye mr-1"></i> {{ $vehicle->views_count ?? 0 }} views</span>
                                <span><i class="fas fa-heart mr-1"></i> {{ $vehicle->saves_count ?? 0 }} saves</span>
                                <span><i class="fas fa-envelope mr-1"></i> {{ $vehicle->enquiries_count ?? 0 }} enquiries</span>
                                <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $vehicle->location }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-600">{{ $vehicle->display_price }}</div>
                            <div class="text-sm text-gray-600 mt-1">{{ ucfirst($vehicle->advert_type) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex space-x-4">
                        <button onclick="toggleFavorite()" class="flex-1 bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 transition-colors">
                            <i class="far fa-heart mr-2"></i> Save Vehicle
                        </button>
                        <button onclick="showEnquiryForm()" class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-envelope mr-2"></i> Contact Seller
                        </button>
                        <button onclick="shareVehicle()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition-colors">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                    <div class="prose max-w-none">
                        <p>{{ $vehicle->description }}</p>
                    </div>
                </div>

                <!-- Specifications -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Specifications</h2>
                    <div class="spec-grid">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Make</div>
                            <div class="font-semibold">{{ $vehicle->make->name }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Model</div>
                            <div class="font-semibold">{{ $vehicle->vehicleModel->name }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Year</div>
                            <div class="font-semibold">{{ $vehicle->year }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Mileage</div>
                            <div class="font-semibold">{{ $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : 'N/A' }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Fuel Type</div>
                            <div class="font-semibold">{{ ucfirst($vehicle->fuel_type) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Transmission</div>
                            <div class="font-semibold">{{ ucfirst($vehicle->transmission) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Condition</div>
                            <div class="font-semibold">{{ ucfirst($vehicle->condition) }}</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Colour</div>
                            <div class="font-semibold">{{ $vehicle->colour ?: 'N/A' }}</div>
                        </div>
                        @if($vehicle->engine_size)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Engine Size</div>
                            <div class="font-semibold">{{ $vehicle->engine_size }}</div>
                        </div>
                        @endif
                        @if($vehicle->doors)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Doors</div>
                            <div class="font-semibold">{{ $vehicle->doors }}</div>
                        </div>
                        @endif
                        @if($vehicle->seats)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Seats</div>
                            <div class="font-semibold">{{ $vehicle->seats }}</div>
                        </div>
                        @endif
                        @if($vehicle->service_history)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">Service History</div>
                            <div class="font-semibold">{{ ucfirst($vehicle->service_history) }}</div>
                        </div>
                        @endif
                        @if($vehicle->mot_expiry)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-600">MOT Expiry</div>
                            <div class="font-semibold">{{ $vehicle->mot_expiry->format('M Y') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Images -->
                @if($vehicle->additional_images_urls && count($vehicle->additional_images_urls) > 2)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Images</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($vehicle->additional_images_urls as $image)
                            <div class="cursor-pointer rounded-lg overflow-hidden" onclick="changeMainImage('{{ $image }}')">
                                <img src="{{ $image }}" alt="{{ $vehicle->title }}" class="w-full h-32 object-cover hover:opacity-80 transition-opacity">
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Contact and Info -->
            <div class="space-y-6">
                <!-- Seller Information -->
                <div class="contact-card rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Seller Information</h2>
                    <div class="text-center mb-6">
                        @if($vehicle->user->business)
                            <img src="{{ $vehicle->user->business->logo_url ?? '/images/default-logo.png' }}" alt="{{ $vehicle->user->business->name }}" class="w-20 h-20 mx-auto rounded-full mb-4">
                            <h3 class="font-semibold text-lg">{{ $vehicle->user->business->name }}</h3>
                        @else
                            <div class="w-20 h-20 mx-auto bg-gray-300 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-user text-3xl text-gray-600"></i>
                            </div>
                            <h3 class="font-semibold text-lg">{{ $vehicle->user->name }}</h3>
                        @endif
                        @if($vehicle->is_verified_seller)
                            <div class="mt-2">
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i> Verified Seller
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-phone mr-3"></i>
                            <span>{{ $vehicle->contact_phone }}</span>
                        </div>
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-envelope mr-3"></i>
                            <span>{{ $vehicle->contact_email }}</span>
                        </div>
                        @if($vehicle->website)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-globe mr-3"></i>
                                <a href="{{ $vehicle->website }}" target="_blank" class="text-indigo-600 hover:underline">Website</a>
                            </div>
                        @endif
                        <div class="flex items-center text-gray-600">
                            <i class="fas fa-map-marker-alt mr-3"></i>
                            <span>{{ $vehicle->full_location }}</span>
                        </div>
                    </div>
                    @if($vehicle->seller_description)
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-600">{{ $vehicle->seller_description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Safety Tips -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">
                        <i class="fas fa-shield-alt mr-2"></i> Safety Tips
                    </h3>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li><i class="fas fa-check mr-2"></i> Meet in a safe, public location</li>
                        <li><i class="fas fa-check mr-2"></i> Verify the vehicle's documents</li>
                        <li><i class="fas fa-check mr-2"></i> Test drive before purchasing</li>
                        <li><i class="fas fa-check mr-2"></i> Never share financial information</li>
                        <li><i class="fas fa-check mr-2"></i> Trust your instincts</li>
                    </ul>
                </div>

                <!-- Report Ad -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <button onclick="reportAd()" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-flag mr-2"></i> Report This Ad
                    </button>
                </div>

                <!-- Similar Vehicles -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Similar Vehicles</h3>
                    <div id="similarVehicles" class="space-y-4">
                        <!-- Similar vehicles will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enquiry Modal -->
    <div id="enquiryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Contact Seller</h3>
            <form id="enquiryForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Your Name *</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Your Email *</label>
                    <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Your Phone</label>
                    <input type="tel" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message *</label>
                    <textarea name="message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        Send Message
                    </button>
                    <button type="button" onclick="hideEnquiryForm()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let isFavorited = false;

        // Load similar vehicles
        document.addEventListener('DOMContentLoaded', function() {
            loadSimilarVehicles();
            checkFavoriteStatus();
        });

        function changeMainImage(imageUrl) {
            document.getElementById('mainImage').src = imageUrl;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.closest('.thumbnail').classList.add('active');
        }

        function showEnquiryForm() {
            document.getElementById('enquiryModal').classList.remove('hidden');
        }

        function hideEnquiryForm() {
            document.getElementById('enquiryModal').classList.add('hidden');
        }

        // Enquiry form submission
        document.getElementById('enquiryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(`/api/vehicles/{{ $vehicle->id }}/enquiry`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                if (response.ok) {
                    alert('Your message has been sent to the seller!');
                    hideEnquiryForm();
                    this.reset();
                } else {
                    const error = await response.json();
                    alert('Error sending message: ' + error.message);
                }
            } catch (error) {
                console.error('Error sending enquiry:', error);
                alert('Error sending message. Please try again.');
            }
        });

        async function toggleFavorite() {
            try {
                const response = await fetch(`/api/vehicles/{{ $vehicle->id }}/save`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    isFavorited = !isFavorited;
                    updateFavoriteButton();
                    alert(result.message);
                } else {
                    // Redirect to login if not authenticated
                    window.location.href = '/login';
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
        }

        function updateFavoriteButton() {
            const button = document.querySelector('button[onclick="toggleFavorite()"]');
            if (isFavorited) {
                button.innerHTML = '<i class="fas fa-heart mr-2"></i> Saved';
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-gray-600', 'hover:bg-gray-700');
            } else {
                button.innerHTML = '<i class="far fa-heart mr-2"></i> Save Vehicle';
                button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                button.classList.add('bg-red-600', 'hover:bg-red-700');
            }
        }

        async function checkFavoriteStatus() {
            try {
                const response = await fetch(`/api/vehicles/saved`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    const savedVehicles = await response.json();
                    isFavorited = savedVehicles.data.some(vehicle => vehicle.id === {{ $vehicle->id }});
                    updateFavoriteButton();
                }
            } catch (error) {
                // User not logged in or other error
            }
        }

        async function loadSimilarVehicles() {
            try {
                const response = await fetch(`/api/vehicles/{{ $vehicle->id }}/related`);
                const vehicles = await response.json();
                
                const container = document.getElementById('similarVehicles');
                container.innerHTML = '';
                
                vehicles.data.forEach(vehicle => {
                    const card = document.createElement('div');
                    card.className = 'flex space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer';
                    card.innerHTML = `
                        <img src="${vehicle.main_image_url || '/images/placeholder.png'}" alt="${vehicle.title}" class="w-20 h-20 object-cover rounded">
                        <div class="flex-1">
                            <h4 class="font-semibold text-sm">${vehicle.title}</h4>
                            <p class="text-xs text-gray-600">${vehicle.make?.name} ${vehicle.vehicleModel?.name}</p>
                            <p class="text-sm font-bold text-indigo-600">${vehicle.display_price}</p>
                        </div>
                    `;
                    card.addEventListener('click', () => {
                        window.location.href = `/vehicles/${vehicle.id}`;
                    });
                    container.appendChild(card);
                });
            } catch (error) {
                console.error('Error loading similar vehicles:', error);
            }
        }

        function shareVehicle() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $vehicle->title }}',
                    text: 'Check out this vehicle on WWA',
                    url: window.location.href
                });
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard!');
            }
        }

        function reportAd() {
            // Implement report functionality
            alert('Report functionality will be available soon. Please contact support directly.');
        }

        // Close modal when clicking outside
        document.getElementById('enquiryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideEnquiryForm();
            }
        });
    </script>
</body>
</html>
