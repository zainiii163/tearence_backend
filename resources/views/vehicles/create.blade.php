<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Vehicle Advert - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: #4f46e5;
            color: white;
        }
        .step-indicator.completed {
            background: #10b981;
            color: white;
        }
        .upgrade-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .upgrade-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .upgrade-card.selected {
            border-color: #4f46e5;
            background: #f0f9ff;
        }
        .upgrade-card.most-popular {
            border-color: #f59e0b;
            position: relative;
        }
        .upgrade-card.most-popular::before {
            content: "MOST POPULAR";
            position: absolute;
            top: -12px;
            right: 20px;
            background: #f59e0b;
            color: white;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
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
                    <span class="ml-2 text-gray-600">Post Vehicle Advert</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/vehicles" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Vehicles
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Progress Steps -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1" class="step-indicator active w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">1</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Advert Type</p>
                        <p class="text-xs text-gray-500">Choose your advert type</p>
                    </div>
                </div>
                <div class="flex-1 h-px bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step2" class="step-indicator w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium">2</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Vehicle Details</p>
                        <p class="text-xs text-gray-500">Basic information</p>
                    </div>
                </div>
                <div class="flex-1 h-px bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step3" class="step-indicator w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium">3</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Specifications</p>
                        <p class="text-xs text-gray-500">Technical details</p>
                    </div>
                </div>
                <div class="flex-1 h-px bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step4" class="step-indicator w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium">4</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Media & Location</p>
                        <p class="text-xs text-gray-500">Photos and location</p>
                    </div>
                </div>
                <div class="flex-1 h-px bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step5" class="step-indicator w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium">5</div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Upgrades</p>
                        <p class="text-xs text-gray-500">Promotion options</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Advert Type -->
        <div id="step1Content" class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Select Advert Type</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="advert-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-500 hover:shadow-lg transition-all" data-type="sale">
                    <div class="text-center">
                        <i class="fas fa-car text-4xl text-indigo-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Vehicle for Sale</h3>
                        <p class="text-sm text-gray-600 mt-2">Sell your car, van, bike, or any vehicle</p>
                    </div>
                </div>
                <div class="advert-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-500 hover:shadow-lg transition-all" data-type="hire">
                    <div class="text-center">
                        <i class="fas fa-key text-4xl text-green-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Vehicle for Hire</h3>
                        <p class="text-sm text-gray-600 mt-2">Rent out your vehicle by day, week, or month</p>
                    </div>
                </div>
                <div class="advert-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-500 hover:shadow-lg transition-all" data-type="lease">
                    <div class="text-center">
                        <i class="fas fa-handshake text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Vehicle for Lease</h3>
                        <p class="text-sm text-gray-600 mt-2">Long-term leasing options available</p>
                    </div>
                </div>
                <div class="advert-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-500 hover:shadow-lg transition-all" data-type="transport_service">
                    <div class="text-center">
                        <i class="fas fa-bus text-4xl text-purple-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Transport Service</h3>
                        <p class="text-sm text-gray-600 mt-2">Taxi, chauffeur, shuttle services</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Basic Information -->
        <div id="step2Content" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Basic Vehicle Information</h2>
            <form id="vehicleForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title *</label>
                        <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tagline</label>
                        <input type="text" name="tagline" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category *</label>
                        <select name="vehicle_category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a category</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Make *</label>
                        <select name="make_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select make</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Model *</label>
                        <select name="model_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select model</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year *</label>
                        <input type="number" name="year" min="1900" max="2025" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mileage</label>
                        <input type="number" name="mileage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fuel Type</label>
                        <select name="fuel_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select fuel type</option>
                            <option value="petrol">Petrol</option>
                            <option value="diesel">Diesel</option>
                            <option value="electric">Electric</option>
                            <option value="hybrid">Hybrid</option>
                            <option value="lpg">LPG</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Transmission</label>
                        <select name="transmission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select transmission</option>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                            <option value="semi-automatic">Semi-Automatic</option>
                            <option value="cvt">CVT</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Condition *</label>
                        <select name="condition" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select condition</option>
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Colour</label>
                        <input type="text" name="colour" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Body Type</label>
                        <select name="body_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select body type</option>
                            <option value="saloon">Saloon</option>
                            <option value="hatchback">Hatchback</option>
                            <option value="suv">SUV</option>
                            <option value="mpv">MPV</option>
                            <option value="coupe">Coupe</option>
                            <option value="convertible">Convertible</option>
                            <option value="pickup">Pickup</option>
                            <option value="van">Van</option>
                            <option value="truck">Truck</option>
                            <option value="bus">Bus</option>
                            <option value="motorbike">Motorbike</option>
                            <option value="boat">Boat</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Step 3: Specifications -->
        <div id="step3Content" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Vehicle Specifications</h2>
            
            <!-- Pricing Section -->
            <div class="border-b pb-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price *</label>
                        <input type="number" name="price" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price Type *</label>
                        <select name="price_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="fixed">Fixed Price</option>
                            <option value="per_day">Per Day</option>
                            <option value="per_week">Per Week</option>
                            <option value="per_month">Per Month</option>
                            <option value="negotiable">Negotiable</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Negotiable</label>
                        <div class="mt-2">
                            <input type="checkbox" name="negotiable" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Price is negotiable</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Car Specifications -->
            <div id="carSpecs" class="spec-section border-b pb-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Car Specifications</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Engine Size</label>
                        <input type="text" name="engine_size" placeholder="e.g., 2.0L" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Doors</label>
                        <input type="number" name="doors" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Seats</label>
                        <input type="number" name="seats" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Previous Owners</label>
                        <input type="number" name="previous_owners" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service History</label>
                        <select name="service_history" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select service history</option>
                            <option value="full">Full Service History</option>
                            <option value="partial">Partial Service History</option>
                            <option value="none">No Service History</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">MOT Expiry</label>
                        <input type="date" name="mot_expiry" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Road Tax Status</label>
                        <input type="text" name="road_tax_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Commercial Vehicle Specifications -->
            <div id="commercialSpecs" class="spec-section border-b pb-6 mb-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Commercial Vehicle Specifications</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payload Capacity (kg)</label>
                        <input type="number" name="payload" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Axles</label>
                        <input type="number" name="axles" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Emission Class</label>
                        <input type="text" name="emission_class" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fleet Options</label>
                        <div class="mt-2">
                            <input type="checkbox" name="fleet_options" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Available for fleet</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boat Specifications -->
            <div id="boatSpecs" class="spec-section border-b pb-6 mb-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Boat Specifications</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Engine Type</label>
                        <select name="engine_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select engine type</option>
                            <option value="inboard">Inboard</option>
                            <option value="outboard">Outboard</option>
                            <option value="jet">Jet</option>
                            <option value="sail">Sail</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Length (meters)</label>
                        <input type="number" name="length" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Capacity</label>
                        <input type="number" name="capacity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Trailer Included</label>
                        <div class="mt-2">
                            <input type="checkbox" name="trailer_included" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Trailer included</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transport Service Specifications -->
            <div id="transportSpecs" class="spec-section hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transport Service Specifications</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Service Area</label>
                        <textarea name="service_area" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Operating Hours</label>
                        <input type="text" name="operating_hours" placeholder="e.g., 9:00 AM - 6:00 PM" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Passenger Capacity</label>
                        <input type="number" name="passenger_capacity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Luggage Capacity</label>
                        <input type="number" name="luggage_capacity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Airport Pickup</label>
                        <div class="mt-2">
                            <input type="checkbox" name="airport_pickup" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Airport pickup available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Media & Location -->
        <div id="step4Content" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Media & Location</h2>
            
            <!-- Media Upload -->
            <div class="border-b pb-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vehicle Images</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Main Image *</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="main_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload a file</span>
                                        <input id="main_image" name="main_image" type="file" class="sr-only" accept="image/*" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional Images</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-images text-3xl text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="additional_images" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload files</span>
                                        <input id="additional_images" name="additional_images" type="file" class="sr-only" accept="image/*" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each (max 15 images)</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Video Link</label>
                        <input type="url" name="video_link" placeholder="YouTube or video link" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country *</label>
                        <input type="text" name="country" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <input type="text" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                        <input type="number" name="latitude" step="0.000001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                        <input type="number" name="longitude" step="0.000001" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Show Exact Location</label>
                        <div class="mt-2">
                            <input type="checkbox" name="show_exact_location" checked class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Show exact location to users</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Upgrades -->
        <div id="step5Content" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Upgrade Your Advert</h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Featured vehicles get 4× more enquiries on average.</strong> Upgrade your advert to get maximum visibility and reach more potential buyers.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Standard -->
                <div class="upgrade-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer" data-upgrade="standard">
                    <div class="text-center">
                        <i class="fas fa-tag text-3xl text-gray-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Standard</h3>
                        <p class="text-2xl font-bold text-gray-900 my-4">Free</p>
                        <ul class="text-sm text-gray-600 text-left space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Basic listing</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>30 days duration</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Standard visibility</li>
                            <li><i class="fas fa-times text-red-500 mr-2"></i>No promotion</li>
                        </ul>
                    </div>
                </div>

                <!-- Promoted -->
                <div class="upgrade-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer" data-upgrade="promoted">
                    <div class="text-center">
                        <i class="fas fa-star text-3xl text-green-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Promoted</h3>
                        <p class="text-2xl font-bold text-gray-900 my-4">£19.99</p>
                        <ul class="text-sm text-gray-600 text-left space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Highlighted listing</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Above standard ads</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>"Promoted" badge</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>2× more visibility</li>
                        </ul>
                    </div>
                </div>

                <!-- Featured -->
                <div class="upgrade-card most-popular border-2 border-gray-200 rounded-lg p-6 cursor-pointer" data-upgrade="featured">
                    <div class="text-center">
                        <i class="fas fa-crown text-3xl text-blue-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Featured</h3>
                        <p class="text-2xl font-bold text-gray-900 my-4">£49.99</p>
                        <ul class="text-sm text-gray-600 text-left space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Top of category pages</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Larger advert card</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Priority in search</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>4× more visibility</li>
                        </ul>
                    </div>
                </div>

                <!-- Sponsored -->
                <div class="upgrade-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer" data-upgrade="sponsored">
                    <div class="text-center">
                        <i class="fas fa-rocket text-3xl text-red-600 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900">Sponsored</h3>
                        <p class="text-2xl font-bold text-gray-900 my-4">£99.99</p>
                        <ul class="text-sm text-gray-600 text-left space-y-2">
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Homepage placement</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Social media promotion</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>Maximum visibility</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i>6× more visibility</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="overflow-x-auto mb-8">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Features</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Standard</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Promoted</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sponsored</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Listing Duration</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">30 days</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">30 days</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">60 days</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">90 days</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Search Priority</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Standard</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Enhanced</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">High</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Maximum</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Homepage Placement</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-times text-red-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-times text-red-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-check text-green-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-check text-green-500"></i></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Social Media Promotion</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-times text-red-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-times text-red-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-check text-green-500"></i></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><i class="fas fa-check text-green-500"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Contact Information -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Name *</label>
                        <input type="text" name="contact_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Phone *</label>
                        <input type="tel" name="contact_phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Email *</label>
                        <input type="email" name="contact_email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="url" name="website" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
            <button id="prevBtn" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors hidden">
                <i class="fas fa-arrow-left mr-2"></i> Previous
            </button>
            <div class="flex space-x-4 ml-auto">
                <button id="nextBtn" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                    Next <i class="fas fa-arrow-right ml-2"></i>
                </button>
                <button id="submitBtn" class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors hidden">
                    <i class="fas fa-check mr-2"></i> Submit Advert
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let selectedAdvertType = '';
        let selectedUpgrade = 'standard';

        // Step 1: Advert Type Selection
        document.querySelectorAll('.advert-type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.advert-type-card').forEach(c => {
                    c.classList.remove('border-indigo-500', 'bg-indigo-50');
                });
                this.classList.add('border-indigo-500', 'bg-indigo-50');
                selectedAdvertType = this.dataset.type;
                document.getElementById('nextBtn').disabled = false;
            });
        });

        // Step 5: Upgrade Selection
        document.querySelectorAll('.upgrade-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.upgrade-card').forEach(c => {
                    c.classList.remove('selected');
                });
                this.classList.add('selected');
                selectedUpgrade = this.dataset.upgrade;
            });
        });

        // Navigation
        document.getElementById('nextBtn').addEventListener('click', function() {
            if (currentStep === 1 && !selectedAdvertType) {
                alert('Please select an advert type');
                return;
            }
            
            if (currentStep < 5) {
                showStep(currentStep + 1);
            }
        });

        document.getElementById('prevBtn').addEventListener('click', function() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });

        function showStep(step) {
            // Hide current step
            document.getElementById(`step${currentStep}Content`).classList.add('hidden');
            document.getElementById(`step${currentStep}`).classList.remove('active');
            
            // Show new step
            document.getElementById(`step${step}Content`).classList.remove('hidden');
            document.getElementById(`step${step}`).classList.add('active');
            
            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                document.getElementById(`step${i}`).classList.add('completed');
            }
            
            // Update navigation buttons
            document.getElementById('prevBtn').classList.toggle('hidden', step === 1);
            document.getElementById('nextBtn').classList.toggle('hidden', step === 5);
            document.getElementById('submitBtn').classList.toggle('hidden', step !== 5);
            
            // Load data for step 2
            if (step === 2) {
                loadCategories();
                loadMakes();
            }
            
            // Show/hide specification sections based on advert type
            if (step === 3) {
                updateSpecificationSections();
            }
            
            currentStep = step;
        }

        function updateSpecificationSections() {
            // Hide all spec sections first
            document.querySelectorAll('.spec-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show relevant sections based on advert type
            if (['sale', 'hire', 'lease'].includes(selectedAdvertType)) {
                document.getElementById('carSpecs').classList.remove('hidden');
                document.getElementById('commercialSpecs').classList.remove('hidden');
                document.getElementById('boatSpecs').classList.remove('hidden');
            } else if (selectedAdvertType === 'transport_service') {
                document.getElementById('transportSpecs').classList.remove('hidden');
            }
        }

        async function loadCategories() {
            try {
                const response = await fetch('/api/vehicle-categories');
                const categories = await response.json();
                
                const select = document.querySelector('select[name="vehicle_category_id"]');
                select.innerHTML = '<option value="">Select a category</option>';
                
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        async function loadMakes() {
            try {
                const response = await fetch('/api/vehicles/makes');
                const makes = await response.json();
                
                const makeSelect = document.querySelector('select[name="make_id"]');
                makeSelect.innerHTML = '<option value="">Select make</option>';
                
                makes.forEach(make => {
                    const option = document.createElement('option');
                    option.value = make.id;
                    option.textContent = make.name;
                    makeSelect.appendChild(option);
                });
                
                // Add change event to load models
                makeSelect.addEventListener('change', function() {
                    loadModels(this.value);
                });
            } catch (error) {
                console.error('Error loading makes:', error);
            }
        }

        async function loadModels(makeId) {
            if (!makeId) return;
            
            try {
                const response = await fetch(`/api/vehicles/models/${makeId}`);
                const models = await response.json();
                
                const modelSelect = document.querySelector('select[name="model_id"]');
                modelSelect.innerHTML = '<option value="">Select model</option>';
                
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.textContent = model.name;
                    modelSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading models:', error);
            }
        }

        // Form submission
        document.getElementById('submitBtn').addEventListener('click', async function() {
            const formData = new FormData(document.getElementById('vehicleForm'));
            formData.append('advert_type', selectedAdvertType);
            formData.append('upgrade_type', selectedUpgrade);
            
            try {
                const response = await fetch('/api/vehicles', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                if (response.ok) {
                    alert('Vehicle advert posted successfully!');
                    window.location.href = '/vehicles/my-vehicles';
                } else {
                    const error = await response.json();
                    alert('Error posting advert: ' + error.message);
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                alert('Error posting advert. Please try again.');
            }
        });

        // Initialize
        document.getElementById('nextBtn').disabled = true;
    </script>
</body>
</html>
