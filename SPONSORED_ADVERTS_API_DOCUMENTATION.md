# Sponsored Adverts System - Complete Backend Implementation

## Overview

This document provides comprehensive documentation for the Sponsored Adverts system backend implementation, including API endpoints, database structure, models, and integration guidelines.

## Table of Contents

1. [API Endpoints](#api-endpoints)
2. [Database Schema](#database-schema)
3. [Models](#models)
4. [Authentication](#authentication)
5. [Error Handling](#error-handling)
6. [Rate Limiting](#rate-limiting)
7. [File Upload](#file-upload)
8. [Analytics & Tracking](#analytics--tracking)
9. [Frontend Integration](#frontend-integration)
10. [Admin Panel](#admin-panel)
11. [Deployment](#deployment)

## API Endpoints

### Base URLs
- **Production**: `https://api.worldwideadverts.com/v1/sponsored`
- **Development**: `http://localhost:8000/v1/sponsored`

### Authentication
All API endpoints (except public ones) require JWT Bearer token authentication:
```
Authorization: Bearer <jwt_token>
```

### Public Endpoints

#### Categories
- `GET /categories` - List all sponsored categories
- `GET /categories/{slug}` - Get single category details

#### Adverts
- `GET /adverts` - List sponsored adverts with filtering
- `GET /adverts/{id}` - Get single advert details
- `GET /adverts/search` - Search sponsored adverts
- `GET /adverts/featured` - Get featured adverts
- `GET /adverts/category/{slug}` - Get adverts by category

#### Statistics & Analytics
- `GET /stats` - Get platform statistics
- `GET /activity` - Get live activity feed

### Authenticated Endpoints

#### Advert Management
- `POST /adverts` - Create new advert
- `PUT /adverts/{id}` - Update advert
- `DELETE /adverts/{id}` - Delete advert
- `GET /adverts/my-adverts` - Get user's adverts
- `POST /adverts/{advertId}/save` - Save/unsave advert
- `GET /adverts/saved` - Get saved adverts
- `POST /adverts/{advertId}/track` - Track analytics event
- `GET /adverts/{advertId}/analytics` - Get advert analytics

#### Category Management (Admin)
- `POST /categories` - Create category
- `PUT /categories/{id}` - Update category
- `DELETE /categories/{id}` - Delete category

## Database Schema

### sponsored_adverts Table
```sql
CREATE TABLE sponsored_adverts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12,2) NULL,
    currency CHAR(3) DEFAULT 'USD',
    category_id BIGINT NOT NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    images JSON NULL,
    video_url VARCHAR(500) NULL,
    seller_info JSON NULL,
    location JSON NULL,
    views BIGINT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    reviews_count INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    promoted BOOLEAN DEFAULT FALSE,
    sponsored BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'pending', 'expired', 'paused', 'rejected') DEFAULT 'pending',
    promotion_plan ENUM('free', 'promoted', 'featured', 'sponsored') DEFAULT 'free',
    promotion_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_category_id (category_id),
    INDEX idx_country (country),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at),
    INDEX idx_featured (featured),
    INDEX idx_promoted (promoted),
    INDEX idx_sponsored (sponsored),
    FULLTEXT idx_search (title, description)
);
```

### sponsored_categories Table
```sql
CREATE TABLE sponsored_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50) NULL,
    color VARCHAR(50) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### sponsored_analytics Table
```sql
CREATE TABLE sponsored_analytics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    advert_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    event_type ENUM('view', 'click', 'save', 'contact', 'share') NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (advert_id) REFERENCES sponsored_adverts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_advert_event (advert_id, event_type),
    INDEX idx_created_at (created_at)
);
```

### saved_adverts Table
```sql
CREATE TABLE saved_adverts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    advert_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (advert_id) REFERENCES sponsored_adverts(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_advert (user_id, advert_id)
);
```

### sponsored_pricing_plans Table
```sql
CREATE TABLE sponsored_pricing_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    currency CHAR(3) DEFAULT 'USD',
    duration_days INT NOT NULL,
    features JSON NULL,
    active BOOLEAN DEFAULT TRUE,
    recommended BOOLEAN DEFAULT FALSE,
    visibility_multiplier INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Models

### SponsoredAdvert Model
```php
class SponsoredAdvert extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'price', 'currency',
        'category_id', 'country', 'city', 'images', 'video_url',
        'seller_info', 'location', 'views', 'rating', 'reviews_count',
        'featured', 'promoted', 'sponsored', 'status',
        'promotion_plan', 'promotion_expires_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'seller_info' => 'array',
        'location' => 'array',
        'views' => 'integer',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'featured' => 'boolean',
        'promoted' => 'boolean',
        'sponsored' => 'boolean',
        'promotion_expires_at' => 'datetime',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(SponsoredCategory::class); }
    public function analytics() { return $this->hasMany(SponsoredAnalytic::class, 'advert_id'); }
    public function saves() { return $this->hasMany(SavedAdvert::class, 'advert_id'); }

    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeFeatured($query) { return $query->where('featured', true); }
    public function scopePromoted($query) { return $query->where('promoted', true); }
    public function scopeSponsored($query) { return $query->where('sponsored', true); }
    public function scopeByCategory($query, $categoryId) { return $query->where('category_id', $categoryId); }
    public function scopeByCountry($query, $country) { return $query->where('country', $country); }

    // Methods
    public function incrementViews() { /* increment views */ }
    public function trackEvent($eventType, $metadata = [], $userId = null) { /* track analytics event */ }
    public function getFormattedPriceAttribute() { /* format price */ }
    public function getFirstImageUrlAttribute() { /* get first image */ }
    public function getIsCurrentlyPromotedAttribute() { /* check promotion status */ }
}
```

### SponsoredCategory Model
```php
class SponsoredCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'color', 'description'];
    
    public function adverts() { return $this->hasMany(SponsoredAdvert::class); }
    public function getAdvertsCountAttribute() { return $this->adverts()->active()->count(); }
}
```

## Authentication

### JWT Token Authentication
All protected endpoints require a valid JWT token in the Authorization header:
```
Authorization: Bearer <token>
```

### Token Refresh
When access tokens expire, use the refresh endpoint:
```
POST /api/v1/auth/refresh
```

### User Context
The authenticated user ID is automatically extracted from the JWT token and used for:
- Creating adverts
- Updating own adverts
- Deleting own adverts
- Saving/un-saving adverts
- Accessing personal analytics

## Error Handling

### Standard Response Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### HTTP Status Codes
- `200` - Success
- `201` - Created successfully
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (permission denied)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

## Rate Limiting

### Rate Limits
- **Unauthenticated**: 100 requests per hour
- **Authenticated**: 1,000 requests per hour  
- **Premium Users**: 5,000 requests per hour

### Rate Limit Headers
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 950
X-RateLimit-Reset: 1640995200
```

## File Upload

### Image Upload Specifications
- **Allowed Types**: jpg, jpeg, png, gif, webp
- **Maximum Size**: 5MB per image
- **Maximum Images**: 10 per advert
- **Storage Path**: `/var/www/uploads/sponsored/`
- **CDN URL**: `https://cdn.worldwideadverts.com/sponsored/`

### Video Upload Specifications
- **Allowed Formats**: mp4, avi, mov, wmv
- **Maximum Size**: 100MB per video
- **Maximum Duration**: 5 minutes
- **Storage Path**: `/var/www/uploads/sponsored/videos/`

## Analytics & Tracking

### Event Types
- `view` - Page view
- `click` - Link click
- `save` - Advert saved/bookmarked
- `contact` - Contact form submission
- `share` - Social media share

### Automatic Tracking
- View tracking is automatic on advert detail view
- All events include IP address and user agent
- Analytics data available via advert analytics endpoint

## Frontend Integration

### JavaScript API Client
```javascript
const API_BASE_URL = 'http://localhost:8000/v1/sponsored';

// Set up axios instance with auth
const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});

// Example: Get featured adverts
async function getFeaturedAdverts() {
    try {
        const response = await api.get('/adverts/featured');
        return response.data;
    } catch (error) {
        console.error('Error fetching featured adverts:', error);
        throw error;
    }
}

// Example: Create new advert
async function createAdvert(advertData) {
    try {
        const response = await api.post('/adverts', advertData);
        return response.data;
    } catch (error) {
        console.error('Error creating advert:', error);
        throw error;
    }
}
```

### React Component Example
```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

function SponsoredAdvertsList() {
    const [adverts, setAdverts] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        fetchAdverts();
    }, []);

    const fetchAdverts = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/v1/sponsored/adverts');
            setAdverts(response.data.data);
        } catch (error) {
            console.error('Error fetching adverts:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div>
            <h2>Sponsored Adverts</h2>
            {loading ? (
                <div>Loading...</div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {adverts.map(advert => (
                        <div key={advert.id} className="border rounded-lg p-4">
                            <h3>{advert.title}</h3>
                            <p>{advert.description}</p>
                            <div className="text-sm text-gray-600">
                                {advert.price ? `$${advert.price} ${advert.currency}` : 'Free'}
                            </div>
                            <div className="flex gap-2">
                                {advert.featured && <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Featured</span>}
                                {advert.sponsored && <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Sponsored</span>}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
```

## Admin Panel

### Filament Resources
The admin panel includes three main resources:

1. **SponsoredAdvertResource** - Complete CRUD for adverts
2. **SponsoredCategoryResource** - Category management
3. **SponsoredPricingPlanResource** - Pricing plan management

### Dashboard Widgets
1. **SponsoredOverviewWidget** - Statistics overview table
2. **RecentSponsoredAdvertsWidget** - Recent adverts table
3. **SponsoredStatsChartWidget** - 30-day analytics chart

### Admin Features
- **Advanced Filtering**: Filter by status, promotion plan, category
- **Bulk Operations**: Mass approve, delete, feature adverts
- **Visual Analytics**: Charts and statistics widgets
- **Real-time Updates**: Live activity feed
- **Export Capabilities**: Export data to CSV/Excel

## Deployment

### Environment Variables
```env
SPONSORED_DB_CONNECTION=mysql
SPONSORED_DB_HOST=127.0.0.1
SPONSORED_DB_PORT=3306
SPONSORED_DB_DATABASE=worldwideadverts_sponsored
SPONSORED_DB_USERNAME=sponsored_user
SPONSORED_DB_PASSWORD=your_password
```

### Migration Commands
```bash
# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed --class=SponsoredCategorySeeder
php artisan db:seed --class=SponsoredAdvertSeeder
php artisan db:seed --class=SponsoredPricingPlanSeeder
```

### Cache Configuration
```bash
# Clear cache
php artisan cache:clear

# Optimize for production
php artisan optimize
```

## Security Considerations

### Input Validation
- All user inputs are validated using Laravel's validation rules
- SQL injection protection through parameter binding
- XSS protection with proper output escaping

### File Security
- File type validation for uploads
- File size limits enforced
- Secure file storage with random filenames
- Virus scanning integration recommended

### API Security
- JWT token expiration handling
- Rate limiting implemented
- CORS configuration for frontend access
- HTTPS enforcement in production

## Testing

### Unit Testing
```bash
# Run sponsored advert tests
php artisan test --filter=SponsoredAdvertTest

# Run all tests
php artisan test
```

### API Testing
Use the provided Postman collection for comprehensive API testing:
- `WWA_Sponsored_Adverts_API.postman_collection.json`

## Frontend Integration Examples

### Vue.js Components
```javascript
// api/sponsored.js
import axios from 'axios';

const API = {
    getAdverts: (params = {}) => axios.get('/sponsored/adverts', { params }),
    getAdvert: (id) => axios.get(`/sponsored/adverts/${id}`),
    createAdvert: (data) => axios.post('/sponsored/adverts', data),
    updateAdvert: (id, data) => axios.put(`/sponsored/adverts/${id}`, data),
    deleteAdvert: (id) => axios.delete(`/sponsored/adverts/${id}`),
    saveAdvert: (id) => axios.post(`/sponsored/adverts/${id}/save`),
    getSavedAdverts: () => axios.get('/sponsored/adverts/saved'),
    trackEvent: (id, event, data) => axios.post(`/sponsored/adverts/${id}/track`, data),
    getAnalytics: (id) => axios.get(`/sponsored/adverts/${id}/analytics`),
    getCategories: () => axios.get('/sponsored/categories'),
    getStats: () => axios.get('/sponsored/stats'),
    getActivity: () => axios.get('/sponsored/activity')
};
export default API;
```

### React Components
```jsx
// components/SponsoredAdverts.jsx
import React, { useState, useEffect } from 'react';
import API from '../api/sponsored';

function SponsoredAdvertsPage() {
    const [adverts, setAdverts] = useState([]);
    const [filters, setFilters] = useState({
        category: '',
        country: '',
        minPrice: '',
        maxPrice: '',
        featured: false,
        promoted: false,
        sponsored: false
    });

    useEffect(() => {
        const fetchAdverts = async () => {
            const response = await API.getAdverts(filters);
            setAdverts(response.data.data);
        };
        fetchAdverts();
    }, [filters]);

    return (
        <div>
            <h1>Sponsored Adverts</h1>
            
            {/* Filters */}
            <div className="mb-6 p-4 bg-gray-100 rounded-lg">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label>Category</label>
                        <select value={filters.category} onChange={(e) => setFilters({...filters, category: e.target.value})}>
                            <option value="">All Categories</option>
                            {/* Categories will be loaded from API */}
                        </select>
                    </div>
                    
                    <div>
                        <label>Country</label>
                        <input type="text" value={filters.country} onChange={(e) => setFilters({...filters, country: e.target.value})} />
                    </div>
                    
                    <div>
                        <label>Price Range</label>
                        <input type="number" placeholder="Min" value={filters.minPrice} onChange={(e) => setFilters({...filters, minPrice: e.target.value})} />
                        <input type="number" placeholder="Max" value={filters.maxPrice} onChange={(e) => setFilters({...filters, maxPrice: e.target.value})} />
                    </div>
                </div>
                
                <div className="flex gap-2">
                    <button onClick={() => setFilters({...filters, featured: !filters.featured})}>
                        {filters.featured ? 'All' : 'Featured Only'}
                    </button>
                    <button onClick={() => setFilters({...filters, promoted: !filters.promoted})}>
                        {filters.promoted ? 'All' : 'Promoted Only'}
                    </button>
                    <button onClick={() => setFilters({...filters, sponsored: !filters.sponsored})}>
                        {filters.sponsored ? 'All' : 'Sponsored Only'}
                    </button>
                </div>
            </div>

            {/* Adverts Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {adverts.map(advert => (
                    <div key={advert.id} className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div className="relative">
                            {advert.images && advert.images.length > 0 && (
                                <img src={advert.images[0]} alt={advert.title} className="w-full h-48 object-cover" />
                            )}
                            <div className="p-4">
                                <div className="flex justify-between items-start mb-2">
                                    <h3 className="text-lg font-semibold text-gray-900">{advert.title}</h3>
                                    <div className="flex gap-2">
                                        {advert.featured && <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Featured</span>}
                                        {advert.promoted && <span className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Promoted</span>}
                                        {advert.sponsored && <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Sponsored</span>}
                                    </div>
                                </div>
                                
                                <p className="text-gray-600 text-sm mb-2 line-clamp-2">{advert.description}</p>
                                
                                <div className="flex items-center justify-between">
                                    <span className="text-lg font-bold text-gray-900">
                                        {advert.price ? `$${advert.price} ${advert.currency}` : 'Free'}
                                    </span>
                                    <span className="text-sm text-gray-500">
                                        {advert.views} views • {advert.rating} ⭐
                                    </span>
                                </div>
                                
                                <div className="flex gap-2 mt-3">
                                    <button className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        View Details
                                    </button>
                                    <button className="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
        </div>
    );
}

export default SponsoredAdvertsPage;
```

## Support

For technical support and questions about the Sponsored Adverts system:
- **Documentation**: This file provides comprehensive technical documentation
- **API Collection**: Use the provided Postman collection for testing
- **Code Examples**: See the integration examples above
- **Database Schema**: Reference the schema section for table structures

---

**Last Updated**: March 14, 2026
**Version**: 1.0.0
