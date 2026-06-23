# Sponsored Adverts Backend Implementation Guide

## Overview
This document provides complete implementation details for the sponsored adverts backend system with all API endpoints, database schema, and frontend integration instructions.

## 🏗️ Database Schema

### Sponsored Adverts Table
```sql
CREATE TABLE sponsored_adverts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    tagline VARCHAR(500) NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    video_url VARCHAR(500) NULL,
    advert_type ENUM('buy', 'sell', 'rent', 'offer', 'wanted') NOT NULL,
    sponsored_tier ENUM('basic', 'plus', 'premium') NULL,
    status ENUM('pending_payment', 'active', 'paused', 'expired', 'rejected') DEFAULT 'pending_payment',
    images JSON NULL,
    badges JSON NULL,
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Sponsored Categories Table
```sql
CREATE TABLE sponsored_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) NULL,
    description TEXT NULL,
    count INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Sponsored Analytics Table
```sql
CREATE TABLE sponsored_analytics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    advert_id BIGINT NOT NULL,
    event_type ENUM('view', 'click', 'inquiry', 'contact', 'save') NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    referrer VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advert_id) REFERENCES sponsored_adverts(id)
);
```

### Sponsored Live Activity Table
```sql
CREATE TABLE sponsored_live_activity (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    advert_id BIGINT NULL,
    type ENUM('new_advert', 'view', 'contact', 'inquiry', 'click') NOT NULL,
    message TEXT NOT NULL,
    user_id BIGINT NULL,
    category VARCHAR(100) NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (advert_id) REFERENCES sponsored_adverts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 🚀 API Endpoints

### Base URL
```
https://your-domain.com/api/v1/sponsored-adverts
```

### 1. Homepage & Discovery Endpoints

#### GET `/homepage-stats`
Get homepage statistics for sponsored adverts

**Response:**
```json
{
    "success": true,
    "data": {
        "total_adverts": 1256,
        "active_adverts": 892,
        "total_views": 45678,
        "featured_adverts": 45,
        "new_this_week": 23,
        "top_categories": [
            { "category": "Technology", "count": 234 },
            { "category": "Business", "count": 189 },
            { "category": "Real Estate", "count": 156 }
        ]
    }
}
```

#### GET `/categories`
Get all sponsored categories

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Technology",
            "slug": "technology",
            "icon": "laptop",
            "count": 234,
            "description": "Technology sponsored listings",
            "active": true
        }
    ]
}
```

#### GET `/live-activity`
Get live activity feed for sponsored adverts

**Parameters:**
- `limit` (int, optional): Number of items to return (default: 20)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "new_advert",
            "message": "New sponsored advert posted: Premium Laptop for Sale",
            "timestamp": "2024-03-16T10:00:00Z",
            "user": "John Doe",
            "category": "Technology"
        }
    ]
}
```

### 2. Search & Browse Endpoints

#### GET `/`
Get all sponsored adverts with pagination and filtering

**Parameters:**
- `per_page` (int, optional): Items per page (default: 12)
- `page` (int, optional): Page number (default: 1)
- `keyword` (string, optional): Search keyword
- `category` (string, optional): Filter by category
- `country` (string, optional): Filter by country
- `city` (string, optional): Filter by city
- `min_price` (decimal, optional): Minimum price filter
- `max_price` (decimal, optional): Maximum price filter
- `sort_by` (string, optional): Sort field (created_at, views, price)
- `sort_order` (string, optional): Sort order (asc, desc)
- `advert_type` (string, optional): Filter by advert type
- `sponsored_tier` (string, optional): Filter by sponsorship tier

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Premium Laptop for Sale",
            "description": "High-performance laptop with latest specs",
            "price": "1299.99",
            "category": "Technology",
            "country": "USA",
            "city": "New York",
            "images": ["/images/laptop1.jpg"],
            "video_url": null,
            "advert_type": "sell",
            "sponsored_tier": "premium",
            "status": "active",
            "badges": ["Sponsored Premium", "Featured"],
            "featured": true,
            "views": 1234,
            "seller": {
                "id": 123,
                "name": "John Doe",
                "verified": true,
                "rating": 4.8,
                "adsCount": 15
            },
            "created_at": "2024-03-16T10:00:00Z",
            "expires_at": "2024-04-16T10:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 12,
        "total": 56,
        "from": 1,
        "to": 12
    }
}
```

#### GET `/search`
Search sponsored adverts (same parameters as above)

#### GET `/featured`
Get featured sponsored adverts

**Parameters:**
- `limit` (int, optional): Number of items to return (default: 10)

#### GET `/trending`
Get trending sponsored adverts

**Parameters:**
- `limit` (int, optional): Number of items to return (default: 20)

### 3. Advert Details Endpoints

#### GET `/{id}`
Get single sponsored advert by ID

#### GET `/slug/{slug}`
Get single sponsored advert by slug

#### GET `/category/{slug}`
Get adverts by category

#### GET `/country/{country}`
Get adverts by country

### 4. Management Endpoints (Authentication Required)

#### POST `/`
Create new sponsored advert

**Request Body:**
```json
{
    "title": "Premium Laptop for Sale",
    "tagline": "High-performance laptop with latest specs",
    "description": "Detailed description...",
    "category": "Technology",
    "country": "USA",
    "city": "New York",
    "price": 1299.99,
    "video_url": "https://example.com/video",
    "advert_type": "sell",
    "sponsored_tier": "premium",
    "images": ["base64_image_1", "base64_image_2"],
    "seller_info": {
        "name": "John Doe",
        "phone": "+1234567890",
        "email": "john@example.com"
    },
    "location": {
        "address": "123 Main St",
        "latitude": 40.7128,
        "longitude": -74.0060
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "status": "pending_payment",
        "payment_url": "/payment/sponsored/123",
        "message": "Advert created successfully. Please complete payment to activate."
    }
}
```

#### PUT `/{id}`
Update sponsored advert

#### DELETE `/{id}`
Delete sponsored advert

### 5. Interactions Endpoints (Authentication Required)

#### POST `/{id}/inquiry`
Submit inquiry for sponsored advert

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "message": "Is this item still available?",
    "budget": "1500.00"
}
```

#### POST `/{id}/rating`
Submit rating for sponsored advert

**Request Body:**
```json
{
    "rating": 5,
    "review": "Excellent seller and product!",
    "transaction_id": "txn_123456"
}
```

#### POST `/{advertId}/save`
Save/unsave advert

#### GET `/saved`
Get saved adverts

### 6. Analytics Endpoints

#### POST `/analytics/track`
Track analytics event (internal use)

**Request Body:**
```json
{
    "advert_id": 123,
    "event_type": "view",
    "metadata": {
        "source": "card_click",
        "device": "desktop",
        "category": "Technology"
    }
}
```

#### GET `/statistics`
Get detailed statistics for user's sponsored adverts

### 7. Utility Endpoints

#### POST `/upload`
Upload file for sponsored advert

**Request:** multipart/form-data
- `file`: File to upload
- `type`: File type (image, video)

**Response:**
```json
{
    "success": true,
    "data": {
        "url": "https://your-domain.com/storage/sponsored-adverts/filename.jpg",
        "filename": "filename.jpg",
        "size": 1024000,
        "type": "image"
    }
}
```

## 💳 Sponsored Tiers Configuration

### Basic Tier ($29.99/month)
- **Visibility:** 3x Standard
- **Placement:** Sponsored Page Only
- **Badge:** Sponsored Badge
- **Analytics:** Basic
- **Support:** Email Only
- **Duration:** 30 days

### Plus Tier ($59.99/month) - Most Popular
- **Visibility:** 5x Standard
- **Placement:** Top of Category
- **Badge:** Plus Badge
- **Analytics:** Advanced
- **Support:** Priority Email
- **Duration:** 60 days

### Premium Tier ($99.99/month) - VIP
- **Visibility:** 10x Standard
- **Placement:** Homepage & Top of Category
- **Badge:** Premium VIP Badge
- **Analytics:** Real-time + Insights
- **Support:** Dedicated Account Manager
- **Duration:** 90 days

## 🔧 Frontend Integration Guide

### 1. Authentication
All protected endpoints require Bearer token authentication:

```javascript
// Set up axios with authentication
const api = axios.create({
    baseURL: 'https://your-domain.com/api/v1/sponsored-adverts',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});
```

### 2. Fetching Categories
```javascript
// Get all categories
const getCategories = async () => {
    try {
        const response = await api.get('/categories');
        return response.data.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
        throw error;
    }
};
```

### 3. Creating Adverts
```javascript
// Create new sponsored advert
const createAdvert = async (advertData) => {
    try {
        const response = await api.post('/', advertData);
        
        // Redirect to payment if needed
        if (response.data.data.payment_url) {
            window.location.href = response.data.data.payment_url;
        }
        
        return response.data;
    } catch (error) {
        console.error('Error creating advert:', error);
        throw error;
    }
};
```

### 4. Searching Adverts
```javascript
// Search with filters
const searchAdverts = async (filters) => {
    try {
        const response = await api.get('/', { params: filters });
        return {
            adverts: response.data.data,
            pagination: response.data.meta
        };
    } catch (error) {
        console.error('Error searching adverts:', error);
        throw error;
    }
};

// Usage
const results = await searchAdverts({
    keyword: 'laptop',
    category: 'technology',
    min_price: 500,
    max_price: 2000,
    per_page: 20,
    page: 1
});
```

### 5. File Upload
```javascript
// Upload images or videos
const uploadFile = async (file, type) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    
    try {
        const response = await api.post('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response.data.data.url;
    } catch (error) {
        console.error('Error uploading file:', error);
        throw error;
    }
};
```

### 6. Tracking Analytics
```javascript
// Track advert views
const trackView = async (advertId) => {
    try {
        await api.post('/analytics/track', {
            advert_id: advertId,
            event_type: 'view',
            metadata: {
                source: 'card_click',
                device: navigator.userAgent,
                timestamp: new Date().toISOString()
            }
        });
    } catch (error) {
        console.error('Error tracking view:', error);
    }
};

// Track inquiries
const trackInquiry = async (advertId, inquiryData) => {
    try {
        await api.post(`/${advertId}/inquiry`, inquiryData);
    } catch (error) {
        console.error('Error submitting inquiry:', error);
        throw error;
    }
};
```

### 7. Live Activity Feed
```javascript
// Get live activity
const getLiveActivity = async (limit = 20) => {
    try {
        const response = await api.get('/live-activity', {
            params: { limit }
        });
        return response.data.data;
    } catch (error) {
        console.error('Error fetching live activity:', error);
        throw error;
    }
};

// Real-time updates with WebSocket (optional)
const setupLiveUpdates = () => {
    const ws = new WebSocket('wss://your-domain.com/ws/live-activity');
    
    ws.onmessage = (event) => {
        const activity = JSON.parse(event.data);
        // Update your live activity UI
        updateLiveActivityFeed(activity);
    };
};
```

## 📊 Error Handling

### Standard Error Response Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": "Validation error message"
    },
    "status": 400
}
```

### Common Error Codes
- `400` - Validation Error
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity
- `429` - Too Many Requests
- `500` - Internal Server Error

## 🚀 Rate Limiting

### Endpoints Limits
- **Search:** 100 requests per minute per IP
- **Advert Creation:** 5 per hour per user
- **Inquiry Submission:** 20 per hour per user
- **File Upload:** 10 per hour per user

### Headers
```javascript
// Check rate limit status
const checkRateLimit = (response) => {
    const remaining = response.headers['x-ratelimit-remaining'];
    const reset = response.headers['x-ratelimit-reset'];
    
    if (remaining < 5) {
        console.warn('Rate limit almost exceeded');
    }
};
```

## 🔒 Security Measures

### Input Validation
- All inputs are validated using Laravel's validation rules
- SQL injection protection through Eloquent ORM
- XSS protection with input sanitization
- File type and size validation

### Authentication
- JWT-based authentication required for protected endpoints
- Token expiration handling
- Secure password hashing

### CORS Configuration
```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['*'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
];
```

## 📱 Mobile App Integration

### React Native Example
```javascript
import axios from 'axios';

const sponsoredAPI = axios.create({
    baseURL: 'https://your-domain.com/api/v1/sponsored-adverts',
    timeout: 10000,
});

// Add request interceptor for auth
sponsoredAPI.interceptors.request.use(config => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Get featured adverts
const getFeaturedAdverts = async () => {
    const response = await sponsoredAPI.get('/featured', {
        params: { limit: 10 }
    });
    return response.data.data;
};
```

### Flutter Example
```dart
import 'package:dio/dio.dart';

class SponsoredAdvertsAPI {
    final Dio _dio = Dio();
    
    SponsoredAdvertsAPI() {
        _dio.options.baseUrl = 'https://your-domain.com/api/v1/sponsored-adverts';
        _dio.options.headers = {
            'Content-Type': 'application/json',
        };
    }
    
    Future<List<Advert>> getAdverts(Map<String, dynamic> params) async {
        try {
            final response = await _dio.get('/', queryParameters: params);
            return List<Advert>.from(response.data['data']);
        } catch (e) {
            throw Exception('Failed to load adverts: $e');
        }
    }
}
```

## 🎯 Best Practices

### 1. Caching
```javascript
// Implement client-side caching for categories
const cachedCategories = localStorage.getItem('sponsored_categories');
if (cachedCategories) {
    return JSON.parse(cachedCategories);
}

// Cache search results
const searchCache = new Map();
const getCachedSearch = (key) => {
    return searchCache.get(key);
};
```

### 2. Pagination
```javascript
// Handle infinite scrolling
const loadMoreAdverts = async (page) => {
    const response = await searchAdverts({ page });
    appendAdverts(response.data.adverts);
    
    if (response.data.pagination.current_page >= response.data.pagination.last_page) {
        setHasMore(false);
    }
};
```

### 3. Error Boundaries
```javascript
// React error boundary example
class SponsoredAdvertsErrorBoundary extends React.Component {
    componentDidCatch(error, errorInfo) {
        console.error('Sponsored Adverts Error:', error, errorInfo);
        // Send error to analytics
        trackError(error, errorInfo);
    }
    
    render() {
        if (this.state.hasError) {
            return <ErrorFallback />;
        }
        return this.props.children;
    }
}
```

## 🧪 Testing

### Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed categories
php artisan db:seed --class=SponsoredCategoriesSeeder
```

### API Testing
```bash
# Test homepage stats
curl -X GET "http://localhost:8000/api/v1/sponsored-adverts/homepage-stats"

# Test advert creation
curl -X POST "http://localhost:8000/api/v1/sponsored-adverts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Advert","description":"Test Description","category":"technology","country":"USA","city":"New York","price":99.99,"advert_type":"sell","sponsored_tier":"basic"}'
```

## 📈 Monitoring & Analytics

### Performance Monitoring
- Response time tracking
- Error rate monitoring
- Database query optimization
- Cache hit ratios

### Business Metrics
- Advert creation rates
- Conversion tracking
- Revenue per tier
- User engagement metrics

---

## 🎉 Ready for Production!

The sponsored adverts backend system is now fully implemented and ready for frontend integration. All endpoints follow RESTful conventions, include proper validation, authentication, and error handling.

For support or questions, refer to the API documentation or contact the development team.
