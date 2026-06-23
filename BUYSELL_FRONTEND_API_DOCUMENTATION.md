# BuySell API Frontend Integration Documentation

## Overview

This document provides comprehensive API documentation for integrating the BuySell functionality into your frontend application. The BuySell system allows users to buy and sell various items with advanced filtering, search, and interaction features.

## Base URL

```
https://your-domain.com/api/v1/buysell
```

## Authentication

Most endpoints require authentication using Bearer tokens:

```javascript
headers: {
    'Authorization': 'Bearer ' + authToken,
    'Content-Type': 'application/json'
}
```

## Public Endpoints (No Authentication Required)

### 1. Get All Adverts
**Endpoint:** `GET /api/v1/buysell`

Retrieve a paginated list of all active adverts with optional filtering.

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `limit` (integer): Items per page, max 50 (default: 20)
- `category` (string): Filter by category ID
- `subcategory` (string): Filter by subcategory ID
- `search` (string): Search in title and description
- `condition` (string): Filter by condition ('new', 'used', 'refurbished')
- `price_min` (float): Minimum price filter
- `price_max` (float): Maximum price filter
- `country` (string): Filter by country
- `city` (string): Filter by city
- `user_id` (string): Filter by user ID
- `featured` (boolean): Show only featured items
- `promoted` (boolean): Show only promoted items
- `sponsored` (boolean): Show only sponsored items
- `sortBy` (string): Sort by field ('created_at', 'price', 'views_count', 'title')
- `sortOrder` (string): Sort order ('asc', 'desc')

**Example Request:**
```javascript
fetch('/api/v1/buysell?search=iphone&category=electronics&price_min=100&price_max=500&sortBy=price&sortOrder=asc')
```

**Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": "uuid-string",
                "title": "iPhone 12 Pro",
                "description": "Excellent condition iPhone 12 Pro...",
                "price": 450.00,
                "currency": "USD",
                "condition": "used",
                "negotiable": true,
                "country": "USA",
                "city": "New York",
                "views_count": 25,
                "status": "active",
                "featured": false,
                "promoted": true,
                "created_at": "2024-01-15T10:30:00Z",
                "updated_at": "2024-01-16T14:20:00Z",
                "images": [
                    {
                        "id": "uuid",
                        "image_path": "/storage/images/iphone1.jpg",
                        "is_primary": true
                    }
                ],
                "category": {
                    "id": "uuid",
                    "name": "Electronics",
                    "slug": "electronics"
                },
                "subcategory": {
                    "id": "uuid",
                    "name": "Mobile Phones",
                    "slug": "mobile-phones"
                },
                "user": {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com"
                }
            }
        ],
        "pagination": {
            "currentPage": 1,
            "totalPages": 5,
            "totalItems": 98,
            "itemsPerPage": 20,
            "hasNextPage": true,
            "hasPrevPage": false
        }
    }
}
```

### 2. Get Single Advert
**Endpoint:** `GET /api/v1/buysell/{id}`

Retrieve detailed information about a specific advert.

**Response:** Same structure as individual item in the list response, but with all details including additional fields.

### 3. Get Categories
**Endpoint:** `GET /api/v1/buysell/categories`

Retrieve all active categories.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "name": "Electronics",
            "slug": "electronics",
            "description": "Electronic items and gadgets",
            "icon": "electronics-icon.svg",
            "is_active": true,
            "parent_id": null,
            "subcategories_count": 5,
            "active_adverts_count": 45
        }
    ]
}
```

### 4. Get Subcategories
**Endpoint:** `GET /api/v1/buysell/categories/{categoryId}/subcategories`

Retrieve subcategories for a specific category.

### 5. Get Promotion Plans
**Endpoint:** `GET /api/v1/buysell/promotion-plans`

Retrieve available promotion plans for adverts.

### 6. Search Suggestions
**Endpoint:** `GET /api/v1/buysell/search-suggestions`

Get search suggestions based on query.

**Query Parameters:**
- `q` (string): Search query

### 7. Get Trending Items
**Endpoint:** `GET /api/v1/buysell/trending`

Retrieve trending items based on views and interactions.

## Protected Endpoints (Authentication Required)

### 1. Create Advert
**Endpoint:** `POST /api/v1/buysell/adverts`

Create a new advert.

**Request Body:**
```json
{
    "title": "iPhone 12 Pro",
    "description": "Excellent condition iPhone 12 Pro with original accessories...",
    "category_id": "uuid",
    "subcategory_id": "uuid",
    "condition": "used",
    "price": 450.00,
    "negotiable": true,
    "currency": "USD",
    "country": "USA",
    "city": "New York",
    "state_province": "NY",
    "postal_code": "10001",
    "address": "123 Main St",
    "brand": "Apple",
    "model": "iPhone 12 Pro",
    "color": "Pacific Blue",
    "dimensions": "146.7 x 71.5 x 7.4 mm",
    "weight": "189g",
    "material": "Glass and Aluminum",
    "usage_duration": "1 year",
    "reason_for_selling": "Upgraded to newer model",
    "seller_name": "John Doe",
    "seller_email": "john@example.com",
    "seller_phone": "+1234567890",
    "images": [
        {
            "image_path": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
            "is_primary": true
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Advert created successfully",
    "data": {
        "advert": { /* advert object */ }
    }
}
```

### 2. Update Advert
**Endpoint:** `PUT /api/v1/buysell/adverts/{id}`

Update an existing advert (only by owner).

**Request Body:** Same as create advert, but only include fields to update.

### 3. Delete Advert
**Endpoint:** `DELETE /api/v1/buysell/adverts/{id}`

Delete an advert (only by owner).

### 4. Get My Adverts
**Endpoint:** `GET /api/v1/buysell/adverts/my`

Retrieve adverts created by the authenticated user.

### 5. Save/Unsave Advert
**Endpoint:** `POST /api/v1/buysell/adverts/{id}/save`

Save an advert to user's favorites.

**Endpoint:** `DELETE /api/v1/buysell/adverts/{id}/unsave`

Remove advert from favorites.

### 6. Get Saved Adverts
**Endpoint:** `GET /api/v1/buysell/saved-adverts`

Retrieve user's saved/favorite adverts.

### 7. Contact Seller
**Endpoint:** `POST /api/v1/buysell/adverts/{id}/contact`

Send a message to the seller.

**Request Body:**
```json
{
    "message": "Is this item still available?",
    "phone": "+1234567890",
    "email": "buyer@example.com"
}
```

### 8. Report Advert
**Endpoint:** `POST /api/v1/buysell/adverts/{id}/report`

Report an inappropriate advert.

**Request Body:**
```json
{
    "reason": "spam",
    "description": "This appears to be a spam listing"
}
```

### 9. Track View
**Endpoint:** `POST /api/v1/buysell/adverts/{id}/view`

Track advert view (automatically called when viewing advert details).

**Request Body:** (Optional - server will auto-detect most fields)
```json
{
    "user_agent": "Mozilla/5.0...",
    "referrer": "https://example.com"
}
```

### 10. Get Recently Viewed
**Endpoint:** `GET /api/v1/buysell/recently-viewed`

Get recently viewed adverts by the authenticated user.

### 11. Promote Advert
**Endpoint:** `POST /api/v1/buysell/adverts/{id}/promote`

Promote an advert using a promotion plan.

**Request Body:**
```json
{
    "promotion_plan_id": "uuid",
    "duration_days": 7
}
```

## Data Models

### Advert Object Structure

```json
{
    "id": "uuid",
    "title": "string",
    "description": "text",
    "category_id": "uuid",
    "subcategory_id": "uuid",
    "condition": "new|used|refurbished",
    "price": "decimal",
    "negotiable": "boolean",
    "currency": "string",
    "country": "string",
    "city": "string",
    "state_province": "string",
    "postal_code": "string",
    "address": "string",
    "latitude": "decimal",
    "longitude": "decimal",
    "brand": "string",
    "model": "string",
    "color": "string",
    "dimensions": "string",
    "weight": "string",
    "material": "string",
    "usage_duration": "string",
    "reason_for_selling": "text",
    "seller_name": "string",
    "seller_email": "string",
    "seller_phone": "string",
    "views_count": "integer",
    "status": "active|pending|expired|sold",
    "featured": "boolean",
    "promoted": "boolean",
    "sponsored": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime",
    "images": [
        {
            "id": "uuid",
            "image_path": "string",
            "is_primary": "boolean"
        }
    ],
    "category": {
        "id": "uuid",
        "name": "string",
        "slug": "string"
    },
    "subcategory": {
        "id": "uuid",
        "name": "string",
        "slug": "string"
    },
    "user": {
        "id": "integer",
        "name": "string",
        "email": "string"
    }
}
```

### Category Object Structure

```json
{
    "id": "uuid",
    "name": "string",
    "slug": "string",
    "description": "text",
    "icon": "string",
    "is_active": "boolean",
    "parent_id": "uuid|null",
    "subcategories_count": "integer",
    "active_adverts_count": "integer"
}
```

## Error Handling

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Common HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## File Upload

When creating or updating adverts with images:

1. Convert images to base64 format
2. Include in the `images` array in the request body
3. Mark one image as primary using `is_primary: true`

**Image Upload Example:**
```javascript
const fileInput = document.getElementById('image-input');
const file = fileInput.files[0];

const reader = new FileReader();
reader.onload = function(e) {
    const base64String = e.target.result;
    
    // Send to API
    fetch('/api/v1/buysell/adverts', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            title: 'My Item',
            // ... other fields
            images: [
                {
                    image_path: base64String,
                    is_primary: true
                }
            ]
        })
    });
};
reader.readAsDataURL(file);
```

## Rate Limiting

- Search endpoints: 100 requests per minute
- View tracking: 1000 requests per minute
- Other endpoints: 60 requests per minute

## Caching

- Category data is cached for 1 hour
- Search results are cached for 5 minutes
- Individual adverts are cached for 10 minutes

## WebSocket Events (Optional)

If your application supports real-time updates:

- `advert.created` - New advert created
- `advert.updated` - Advert updated
- `advert.deleted` - Advert deleted
- `advert.sold` - Advert marked as sold

## Integration Examples

### Basic Search Implementation

```javascript
class BuySellAPI {
    constructor(baseURL, authToken) {
        this.baseURL = baseURL;
        this.authToken = authToken;
    }

    async searchAdverts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(`${this.baseURL}?${queryString}`);
        return response.json();
    }

    async getAdvert(id) {
        const response = await fetch(`${this.baseURL}/${id}`);
        return response.json();
    }

    async createAdvert(advertData) {
        const response = await fetch(`${this.baseURL}/adverts`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.authToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(advertData)
        });
        return response.json();
    }

    async trackView(advertId) {
        const response = await fetch(`${this.baseURL}/adverts/${advertId}/view`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.authToken}`,
                'Content-Type': 'application/json'
            }
        });
        return response.json();
    }
}

// Usage
const api = new BuySellAPI('/api/v1/buysell', 'your-auth-token');

// Search for iPhones
const results = await api.searchAdverts({
    search: 'iphone',
    category: 'electronics',
    price_min: 100,
    price_max: 500,
    sortBy: 'price',
    sortOrder: 'asc'
});

// Track advert view
await api.trackView('advert-uuid');
```

### React Component Example

```jsx
import React, { useState, useEffect } from 'react';

const BuySellListing = () => {
    const [adverts, setAdverts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState({});

    useEffect(() => {
        fetchAdverts();
    }, [filters]);

    const fetchAdverts = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/api/v1/buysell?${new URLSearchParams(filters)}`);
            const data = await response.json();
            if (data.success) {
                setAdverts(data.data.items);
            }
        } catch (error) {
            console.error('Error fetching adverts:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const trackAdvertView = async (advertId) => {
        try {
            await fetch(`/api/v1/buysell/adverts/${advertId}/view`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                }
            });
        } catch (error) {
            console.error('Error tracking view:', error);
        }
    };

    return (
        <div>
            {/* Search Filters */}
            <div className="filters">
                <input 
                    type="text" 
                    placeholder="Search..."
                    onChange={(e) => handleFilterChange('search', e.target.value)}
                />
                <select onChange={(e) => handleFilterChange('condition', e.target.value)}>
                    <option value="">All Conditions</option>
                    <option value="new">New</option>
                    <option value="used">Used</option>
                    <option value="refurbished">Refurbished</option>
                </select>
                <input 
                    type="number" 
                    placeholder="Min Price"
                    onChange={(e) => handleFilterChange('price_min', e.target.value)}
                />
                <input 
                    type="number" 
                    placeholder="Max Price"
                    onChange={(e) => handleFilterChange('price_max', e.target.value)}
                />
            </div>

            {/* Adverts List */}
            {loading ? (
                <div>Loading...</div>
            ) : (
                <div className="adverts-grid">
                    {adverts.map(advert => (
                        <div key={advert.id} className="advert-card">
                            <img src={advert.images[0]?.image_path} alt={advert.title} />
                            <h3>{advert.title}</h3>
                            <p className="price">${advert.price}</p>
                            <p className="location">{advert.city}, {advert.country}</p>
                            <button 
                                onClick={() => trackAdvertView(advert.id)}
                                className="view-details-btn"
                            >
                                View Details
                            </button>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default BuySellListing;
```

## Testing

Use the following curl commands for testing:

```bash
# Get all adverts
curl -X GET "http://localhost:8000/api/v1/buysell"

# Search with filters
curl -X GET "http://localhost:8000/api/v1/buysell?search=iphone&category=electronics&price_min=100"

# Get categories
curl -X GET "http://localhost:8000/api/v1/buysell/categories"

# Create advert (with auth)
curl -X POST "http://localhost:8000/api/v1/buysell/adverts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Item","description":"Test description","category_id":"uuid","price":100}'

# Track view
curl -X POST "http://localhost:8000/api/v1/buysell/adverts/UUID/view" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Support

For API support and questions:
- Email: support@your-domain.com
- Documentation: https://docs.your-domain.com
- Status Page: https://status.your-domain.com

---

*Last updated: March 2026*
*Version: 1.0*
