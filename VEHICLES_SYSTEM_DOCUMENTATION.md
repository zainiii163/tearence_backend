# Vehicles Adverts System - Complete Documentation

## Overview

The Vehicles Adverts System is a comprehensive platform for buying, hiring, leasing, and finding transport services. It supports multiple vehicle types including cars, vans, motorbikes, trucks, boats, and transport services with advanced filtering, search capabilities, and premium upgrade options.

## 🏗️ System Architecture

### Database Structure

```
vehicle_categories
├── id, name, slug, description, icon, image
├── is_active, sort_order
└── hasMany(vehicles)

vehicle_makes
├── id, name, slug, country, logo
├── is_active
└── hasMany(models), hasMany(vehicles)

vehicle_models
├── id, make_id, name, slug, category
├── year_start, year_end
├── is_active
└── belongsTo(make), hasMany(vehicles)

vehicles
├── Basic Info: title, tagline, description, advert_type, condition
├── Relations: user_id, business_id, category_id, make_id, model_id
├── Specifications: year, mileage, fuel_type, transmission, engine_size
├── Commercial: payload_capacity, axles, emission_class
├── Boat Specific: length, engine_type, capacity, trailer_included
├── Transport Service: service_area, operating_hours, passenger_capacity
├── Pricing: price, price_type, negotiable, deposit
├── Media: main_image, additional_images[], video_link
├── Location: country, city, address, latitude, longitude
├── Contact: contact_name, contact_phone, contact_email, website
├── Analytics: views, clicks, saves, enquiries
├── Upgrades: is_promoted, is_featured, is_sponsored, is_top_of_category
├── Status: status, is_active, payment_status, expires_at
└── Timestamps: created_at, updated_at

vehicle_favourites
├── user_id, vehicle_id
└── Many-to-Many relationship

vehicle_analytics
├── vehicle_id, event_type, ip_address, user_agent
├── user_id, metadata[]
└── Tracks: view, click, enquiry, save

vehicle_enquiries
├── vehicle_id, user_id, name, email, phone
├── message, status (pending/replied/closed)
└── Communication tracking
```

## 🚀 API Endpoints

### Public Endpoints

#### Vehicles
```http
GET /api/v1/vehicles                    # Browse vehicles with filters
GET /api/v1/vehicles/featured           # Get featured vehicles
GET /api/v1/vehicles/promoted           # Get promoted vehicles  
GET /api/v1/vehicles/sponsored          # Get sponsored vehicles
GET /api/v1/vehicles/recent             # Get recent vehicles
GET /api/v1/vehicles/{id}              # Get vehicle details
GET /api/v1/vehicles/{id}/related       # Get related vehicles
```

#### Data Endpoints
```http
GET /api/v1/vehicles/makes              # Get all vehicle makes
GET /api/v1/vehicles/models/{makeId}    # Get models by make
GET /api/v1/vehicles/categories          # Get all categories
```

### Authenticated Endpoints

#### Vehicle Management
```http
POST /api/v1/vehicles                   # Create new vehicle
PUT /api/v1/vehicles/{id}             # Update vehicle
DELETE /api/v1/vehicles/{id}           # Delete vehicle
GET /api/v1/vehicles/my-vehicles       # User's vehicles
GET /api/v1/vehicles/saved              # User's saved vehicles
POST /api/v1/vehicles/{id}/save        # Save/unsave vehicle
POST /api/v1/vehicles/{id}/toggle-status # Toggle vehicle status
POST /api/v1/vehicles/{id}/mark-sold    # Mark vehicle as sold
POST /api/v1/vehicles/{id}/enquiry     # Send enquiry
```

## 🔍 Advanced Filtering & Search

### Query Parameters
```http
GET /api/v1/vehicles?
    category=1                          # Filter by category
    advert_type=sale                     # Filter by type (sale/hire/lease/transport_service)
    make=Toyota                          # Filter by make name
    model=Camry                          # Filter by model name
    min_price=5000                       # Minimum price
    max_price=50000                      # Maximum price
    min_year=2015                        # Minimum year
    max_year=2023                        # Maximum year
    fuel_type=diesel                     # Fuel type filter
    transmission=automatic                 # Transmission filter
    condition=excellent                   # Condition filter
    country=UK                          # Country filter
    city=London                          # City filter
    search=Toyota Camry                   # Full-text search
    sort_by=price                        # Sort field
    sort_order=asc                        # Sort direction
    with_priority=true                     # Priority ordering for upgrades
    per_page=12                          # Pagination
    page=1                               # Page number
```

### Search Capabilities
- **Full-text search** across title, description, make, model
- **Combined filters** for precise results
- **Priority ordering** for upgraded vehicles
- **Geographic filtering** by country/city
- **Price range filtering** with flexible pricing types

## 💰 Premium Upgrades System

### Upgrade Tiers

#### 1. Promoted (Entry Tier)
- **Benefits**: Highlighted listing, 2× visibility, "Promoted" badge
- **Price**: Entry-level pricing
- **Duration**: 30 days

#### 2. Featured (High Tier) - **Most Popular**
- **Benefits**: Top of category, larger card, priority search, email inclusion
- **Price**: Mid-range pricing
- **Duration**: 30 days

#### 3. Sponsored (Premium Tier)
- **Benefits**: Homepage placement, category top, social media promotion
- **Price**: Premium pricing
- **Duration**: 30 days

#### 4. Top of Category (Ultimate Tier)
- **Benefits**: Always pinned at category top, exclusive badge, newsletters
- **Price**: Highest tier pricing
- **Duration**: 30 days

### Upgrade Implementation
```php
// Database fields
is_promoted: boolean
is_featured: boolean  
is_sponsored: boolean
is_top_of_category: boolean

// Priority ordering
ORDER BY 
    is_top_of_category DESC, 
    is_sponsored DESC, 
    is_featured DESC, 
    is_promoted DESC
```

## 📱 Frontend Integration Guide

### Vehicle Categories Display
```javascript
// Fetch categories
const response = await fetch('/api/v1/vehicles/categories');
const categories = response.data; // [{id, name, slug, icon, vehicles_count}]
```

### Dynamic Make/Model Selection
```javascript
// Fetch makes
const makes = await fetch('/api/v1/vehicles/makes');

// Fetch models when make is selected
const models = await fetch(`/api/v1/vehicles/models/${selectedMakeId}`);

// Update model dropdown
updateModelDropdown(models.data);
```

### Vehicle Listing with Filters
```javascript
const params = new URLSearchParams({
    category: selectedCategory,
    advert_type: selectedType,
    min_price: minPrice,
    max_price: maxPrice,
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: 12,
    with_priority: true
});

const response = await fetch(`/api/v1/vehicles?${params}`);
const vehicles = response.data; // Paginated results
```

### Creating a Vehicle Advert
```javascript
const formData = new FormData();
formData.append('category_id', categoryId);
formData.append('make_id', makeId);
formData.append('model_id', modelId);
formData.append('title', title);
formData.append('description', description);
formData.append('price', price);
formData.append('price_type', 'fixed');
formData.append('country', 'UK');
formData.append('city', 'London');
formData.append('main_image', mainImageFile);
// Add additional images
additionalImages.forEach(img => formData.append('additional_images[]', img));

const response = await fetch('/api/v1/vehicles', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
    },
    body: formData
});
```

### Vehicle Enquiry System
```javascript
const enquiryData = {
    name: user.name,
    email: user.email,
    phone: user.phone,
    message: enquiryMessage
};

const response = await fetch(`/api/v1/vehicles/${vehicleId}/enquiry`, {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(enquiryData)
});
```

## 🎯 Admin Panel Features

### Vehicle Management
- **Full CRUD operations** with comprehensive form fields
- **Image management** (main + 15 additional images)
- **Status management** (pending/approved/rejected)
- **Upgrade management** (promoted/featured/sponsored/top of category)
- **Bulk actions** (approve multiple, mark paid)
- **Advanced filtering** and search capabilities

### Category Management
- **Hierarchical organization** with drag-and-drop sorting
- **Icon and image support** for visual appeal
- **Vehicle count tracking** per category
- **Active/inactive status** management

### Enquiry Management
- **Centralized inquiry handling** from all vehicles
- **Status tracking** (pending/replied/closed)
- **Bulk response actions** for efficiency
- **Communication history** tracking

### Analytics Dashboard
- **View counts** and engagement metrics
- **Enquiry tracking** and conversion rates
- **Popular categories** and vehicle types
- **Geographic distribution** analysis

## 🔧 Technical Implementation Details

### Model Relationships
```php
// Vehicle Model
public function category(): BelongsTo { return $this->belongsTo(VehicleCategory::class); }
public function make(): BelongsTo { return $this->belongsTo(VehicleMake::class); }
public function vehicleModel(): BelongsTo { return $this->belongsTo(VehicleModel::class); }
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function favourites(): HasMany { return $this->hasMany(VehicleFavourite::class); }
public function analytics(): HasMany { return $this->hasMany(VehicleAnalytic::class); }
public function enquiries(): HasMany { return $this->hasMany(VehicleEnquiry::class); }
```

### Advanced Scopes
```php
// Active vehicles only
Vehicle::active()->get();

// Featured vehicles
Vehicle::featured()->active()->get();

// Price range filtering
Vehicle::priceRange(5000, 50000)->get();

// Location filtering
Vehicle::byLocation('UK', 'London')->get();

// Priority ordering
Vehicle::withPriority()->orderBy('created_at', 'desc');
```

### Image Storage
```php
// Storage structure
storage/app/public/vehicles/
├── main_images/
│   ├── vehicle_123_main.jpg
│   └── vehicle_124_main.jpg
└── additional_images/
    ├── vehicle_123_1.jpg
    ├── vehicle_123_2.jpg
    └── vehicle_123_3.jpg

// Access URLs
$url = Storage::url('vehicles/main_images/vehicle_123_main.jpg');
```

## 🚀 Deployment Considerations

### Database Optimization
- **Indexes** on frequently queried fields (category_id, make_id, price, status)
- **Composite indexes** for complex filters
- **Full-text search** indexes for title/description

### Performance Caching
- **Category listings** with vehicle counts
- **Popular makes and models** 
- **Featured vehicles** rotation
- **Search results** for common queries

### Security Measures
- **Input validation** on all endpoints
- **File upload restrictions** (type, size, dimensions)
- **Rate limiting** on sensitive endpoints
- **Authentication middleware** on protected routes

## 📊 Analytics & Reporting

### Tracking Events
- **Page views** with user identification
- **Contact clicks** and enquiry submissions
- **Save/favorite** actions
- **Search queries** and filter usage

### Metrics Dashboard
- **Active vehicles** by category and type
- **Conversion rates** (views → enquiries)
- **Revenue tracking** from upgrades
- **User engagement** patterns

## 🔄 API Response Formats

### Vehicle List Response
```json
{
    "data": [
        {
            "id": 1,
            "title": "2018 Toyota Camry",
            "full_name": "2018 Toyota Camry",
            "display_price": "$15,000",
            "main_image": "https://example.com/storage/vehicles/main.jpg",
            "category": {"id": 1, "name": "Cars"},
            "make": {"id": 1, "name": "Toyota"},
            "vehicle_model": {"id": 1, "name": "Camry"},
            "upgrade_badges": [
                {"text": "Featured", "color": "blue"}
            ],
            "views": 1250,
            "saves": 45,
            "is_favourited": false
        }
    ],
    "links": {
        "first": "https://api.example.com/vehicles?page=1",
        "last": "https://api.example.com/vehicles?page=10",
        "prev": null,
        "next": "https://api.example.com/vehicles?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 12,
        "to": 12,
        "total": 120
    }
}
```

### Single Vehicle Response
```json
{
    "id": 1,
    "title": "2018 Toyota Camry",
    "description": "Well-maintained sedan...",
    "price": 15000.00,
    "display_price": "$15,000",
    "advert_type": "sale",
    "condition": "excellent",
    "year": 2018,
    "mileage": 45000,
    "fuel_type": "petrol",
    "transmission": "automatic",
    "main_image": "https://example.com/storage/vehicles/main.jpg",
    "additional_images": [
        "https://example.com/storage/vehicles/1.jpg",
        "https://example.com/storage/vehicles/2.jpg"
    ],
    "location": "London, UK",
    "contact_name": "John Doe",
    "contact_phone": "+44 20 1234 5678",
    "contact_email": "john@example.com",
    "views": 1250,
    "saves": 45,
    "enquiries": 12,
    "upgrade_badges": [
        {"text": "Featured", "color": "blue"}
    ],
    "is_currently_active": true,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "category": {"id": 1, "name": "Cars", "icon": "car"},
    "make": {"id": 1, "name": "Toyota", "country": "Japan"},
    "vehicle_model": {"id": 1, "name": "Camry", "category": "sedan"}
}
```

## 🎨 Frontend UI Components

### Vehicle Card Component
```jsx
function VehicleCard({ vehicle }) {
    return (
        <div className="vehicle-card">
            <div className="vehicle-image">
                <img src={vehicle.main_image} alt={vehicle.title} />
                {vehicle.upgrade_badges.map(badge => (
                    <span className={`badge badge-${badge.color}`}>
                        {badge.text}
                    </span>
                ))}
            </div>
            <div className="vehicle-info">
                <h3>{vehicle.title}</h3>
                <p className="price">{vehicle.display_price}</p>
                <p className="location">{vehicle.location}</p>
                <div className="stats">
                    <span>👁 {vehicle.views}</span>
                    <span>❤️ {vehicle.saves}</span>
                </div>
            </div>
            <button 
                onClick={() => toggleSave(vehicle.id)}
                className={vehicle.is_favourited ? 'saved' : ''}
            >
                {vehicle.is_favourited ? '❤️ Saved' : '🤍 Save'}
            </button>
        </div>
    );
}
```

### Filter Component
```jsx
function VehicleFilters({ filters, setFilters }) {
    return (
        <div className="filters-panel">
            <select 
                value={filters.category} 
                onChange={(e) => setFilters({...filters, category: e.target.value})}
            >
                <option value="">All Categories</option>
                {categories.map(cat => (
                    <option key={cat.id} value={cat.id}>
                        {cat.name} ({cat.vehicles_count})
                    </option>
                ))}
            </select>
            
            <input 
                type="number" 
                placeholder="Min Price"
                value={filters.min_price}
                onChange={(e) => setFilters({...filters, min_price: e.target.value})}
            />
            
            <input 
                type="number" 
                placeholder="Max Price"
                value={filters.max_price}
                onChange={(e) => setFilters({...filters, max_price: e.target.value})}
            />
            
            <select 
                value={filters.sort_by}
                onChange={(e) => setFilters({...filters, sort_by: e.target.value})}
            >
                <option value="created_at">Most Recent</option>
                <option value="price">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="year">Year: Newest First</option>
            </select>
        </div>
    );
}
```

## 📱 Mobile App Integration

### Responsive Design
- **Mobile-first approach** with progressive enhancement
- **Touch-friendly interfaces** for filtering and navigation
- **Image optimization** for faster loading
- **Offline support** for saved vehicles

### Push Notifications
- **New enquiries** for vehicle owners
- **Price drop alerts** for saved searches
- **Featured vehicle** recommendations
- **Expiry reminders** for vehicle listings

## 🔒 Security & Privacy

### Data Protection
- **GDPR compliance** for user data handling
- **Location privacy** options for users
- **Contact information** protection
- **Secure file uploads** with validation

### Anti-Spam Measures
- **Rate limiting** on enquiries
- **CAPTCHA integration** for public forms
- **Email verification** for user accounts
- **Content moderation** for listings

## 🚀 Future Enhancements

### Planned Features
- **AI-powered recommendations** based on user behavior
- **Multi-currency support** with real-time conversion
- **Vehicle comparison tool** (side-by-side comparison)
- **Finance calculator** integration
- **Virtual tours** and 360° images
- **Instant messaging** between buyers and sellers
- **Vehicle history reports** integration
- **Insurance integration** for instant quotes

### Performance Optimizations
- **Elasticsearch integration** for advanced search
- **CDN implementation** for global image delivery
- **Database sharding** for high-volume traffic
- **Redis caching** for frequently accessed data

---

## 📞 Support & Maintenance

### Monitoring
- **API performance monitoring** with response times
- **Error tracking** and alerting
- **Database performance** optimization
- **User behavior analytics**

### Backup Strategy
- **Daily database backups** with automated testing
- **File storage redundancy** across multiple regions
- **Disaster recovery** planning and testing
- **Data retention policies** compliance

This comprehensive documentation provides everything needed to understand, implement, and maintain the Vehicles Adverts System. The system is designed for scalability, performance, and excellent user experience.
