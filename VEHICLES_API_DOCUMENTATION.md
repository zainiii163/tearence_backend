# Vehicles API Documentation

## Overview
Complete API endpoints for the Vehicles Management System to integrate with frontend applications.

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
Add Bearer Token to Authorization header:
```
Authorization: Bearer {token}
```

---

## 1. Vehicle Categories

### Get All Categories
```http
GET /api/vehicle-categories
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Cars",
            "slug": "cars",
            "is_active": true,
            "created_at": "2026-03-28T07:00:00.000000Z",
            "updated_at": "2026-03-28T07:00:00.000000Z"
        }
    ]
}
```

### Get Active Categories Only
```http
GET /api/vehicle-categories/active
```

---

## 2. Vehicle Makes & Models

### Get All Makes
```http
GET /api/vehicle-makes
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Toyota",
            "slug": "toyota",
            "country": "Japan",
            "is_active": true,
            "models_count": 10
        }
    ]
}
```

### Get Active Makes Only
```http
GET /api/vehicle-makes/active
```

### Get Models by Make
```http
GET /api/vehicle-makes/{make_id}/models
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Corolla",
            "slug": "corolla",
            "make_id": 1,
            "year_start": 2020,
            "year_end": null,
            "category": "sedan",
            "is_active": true
        }
    ]
}
```

---

## 3. Vehicle Listings

### Get All Vehicles (Public)
```http
GET /api/vehicles
```

**Query Parameters:**
- `page` (int) - Page number (default: 1)
- `limit` (int) - Items per page (default: 10)
- `category_id` (int) - Filter by category
- `make_id` (int) - Filter by make
- `model_id` (int) - Filter by model
- `advert_type` (string) - Filter by type (sale, hire, lease, transport_service)
- `condition` (string) - Filter by condition (new, used, excellent, good, fair)
- `price_min` (decimal) - Minimum price
- `price_max` (decimal) - Maximum price
- `year_min` (int) - Minimum year
- `year_max` (int) - Maximum year
- `country` (string) - Filter by country
- `city` (string) - Filter by city
- `search` (string) - Search in title, description
- `sort` (string) - Sort by (created_at, price, year, views)
- `order` (string) - Sort order (asc, desc)
- `featured` (boolean) - Show featured only
- `active` (boolean) - Show active only (default: true)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Toyota Corolla 2020",
            "tagline": "Well maintained family car",
            "description": "Excellent condition...",
            "advert_type": "sale",
            "condition": "good",
            "year": 2020,
            "mileage": 45000,
            "fuel_type": "petrol",
            "transmission": "automatic",
            "engine_size": "1.8L",
            "color": "White",
            "doors": 4,
            "seats": 5,
            "body_type": "sedan",
            "vin": "12345678901234567",
            "registration_number": "ABC-123",
            "price": 15000.00,
            "price_type": "fixed",
            "negotiable": false,
            "deposit": null,
            "country": "USA",
            "city": "New York",
            "address": "123 Main St",
            "latitude": "40.71280000",
            "longitude": "-74.00600000",
            "show_exact_location": true,
            "contact_name": "John Doe",
            "contact_phone": "+1234567890",
            "contact_email": "john@example.com",
            "website": null,
            "features": [
                "Air Conditioning",
                "Power Steering",
                "ABS"
            ],
            "service_history": "Full service history available",
            "mot_expiry": "2024-12-31",
            "road_tax_status": "Valid",
            "previous_owners": 2,
            "status": "approved",
            "is_active": true,
            "is_promoted": false,
            "is_featured": true,
            "is_sponsored": false,
            "is_top_of_category": false,
            "views": 125,
            "clicks": 45,
            "saves": 12,
            "enquiries": 8,
            "main_image": "http://127.0.0.1:8000/storage/vehicles/image1.jpg",
            "additional_images": [
                "http://127.0.0.1:8000/storage/vehicles/image2.jpg",
                "http://127.0.0.1:8000/storage/vehicles/image3.jpg"
            ],
            "video_link": "https://youtube.com/watch?v=example",
            "category": {
                "id": 1,
                "name": "Cars",
                "slug": "cars"
            },
            "make": {
                "id": 1,
                "name": "Toyota",
                "slug": "toyota"
            },
            "model": {
                "id": 1,
                "name": "Corolla",
                "slug": "corolla"
            },
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "+1234567890"
            },
            "pricing_plan": {
                "id": 1,
                "name": "Basic Vehicle Listing",
                "price": 9.99,
                "duration_days": 30
            },
            "created_at": "2026-03-28T07:00:00.000000Z",
            "updated_at": "2026-03-28T07:00:00.000000Z",
            "expires_at": "2026-04-27T07:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 150,
        "last_page": 15,
        "from": 1,
        "to": 10
    }
}
```

### Get Featured Vehicles
```http
GET /api/vehicles/featured
```

### Get Vehicle Details
```http
GET /api/vehicles/{id}
```

### Get My Vehicles (Authenticated User)
```http
GET /api/vehicles/my
Authorization: Bearer {token}
```

---

## 4. Vehicle Management (Authenticated)

### Create Vehicle
```http
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
    "category_id": 1,
    "make_id": 1,
    "model_id": 1,
    "title": "Vehicle Title",
    "tagline": "Vehicle tagline",
    "description": "Detailed description",
    "advert_type": "sale",
    "condition": "good",
    "year": 2020,
    "mileage": 45000,
    "fuel_type": "petrol",
    "transmission": "automatic",
    "engine_size": "1.8L",
    "color": "White",
    "doors": 4,
    "seats": 5,
    "body_type": "sedan",
    "vin": "12345678901234567",
    "registration_number": "ABC-123",
    "payload_capacity": null,
    "axles": null,
    "emission_class": null,
    "length": null,
    "engine_type": null,
    "capacity": null,
    "trailer_included": false,
    "service_area": null,
    "operating_hours": null,
    "passenger_capacity": null,
    "luggage_capacity": null,
    "airport_pickup": false,
    "price": 15000.00,
    "price_type": "fixed",
    "negotiable": false,
    "deposit": null,
    "main_image": "file",
    "additional_images[]": "file",
    "video_link": "https://youtube.com/watch?v=example",
    "country": "USA",
    "city": "New York",
    "address": "123 Main St",
    "latitude": "40.71280000",
    "longitude": "-74.00600000",
    "show_exact_location": true,
    "contact_name": "John Doe",
    "contact_phone": "+1234567890",
    "contact_email": "john@example.com",
    "website": null,
    "features[]": ["Air Conditioning", "Power Steering"],
    "service_history": "Full service history",
    "mot_expiry": "2024-12-31",
    "road_tax_status": "Valid",
    "previous_owners": 2,
    "pricing_plan_id": 1
}
```

### Update Vehicle
```http
PUT /api/vehicles/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Delete Vehicle
```http
DELETE /api/vehicles/{id}
Authorization: Bearer {token}
```

---

## 5. Vehicle Analytics

### Increment Views
```http
POST /api/vehicles/{id}/view
```

**Response:**
```json
{
    "success": true,
    "message": "View counted",
    "views": 126
}
```

### Increment Clicks
```http
POST /api/vehicles/{id}/click
```

### Get Vehicle Analytics
```http
GET /api/vehicles/{id}/analytics
```

**Response:**
```json
{
    "success": true,
    "data": {
        "views": 126,
        "clicks": 46,
        "saves": 12,
        "enquiries": 8,
        "daily_views": [
            {"date": "2026-03-28", "views": 15},
            {"date": "2026-03-27", "views": 12}
        ]
    }
}
```

---

## 6. Vehicle Favourites

### Add to Favourites
```http
POST /api/vehicles/{id}/favourite
Authorization: Bearer {token}
```

### Remove from Favourites
```http
DELETE /api/vehicles/{id}/favourite
Authorization: Bearer {token}
```

### Get My Favourites
```http
GET /api/vehicles/favourites
Authorization: Bearer {token}
```

### Check if Favourited
```http
GET /api/vehicles/{id}/favourite/check
Authorization: Bearer {token}
```

---

## 7. Vehicle Enquiries

### Submit Enquiry
```http
POST /api/vehicles/{id}/enquire
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "message": "Is this vehicle still available?",
    "contact_phone": "+1234567890",
    "contact_email": "user@example.com"
}
```

### Get Vehicle Enquiries (Owner)
```http
GET /api/vehicles/{id}/enquiries
Authorization: Bearer {token}
```

### Get My Enquiries
```http
GET /api/enquiries/my
Authorization: Bearer {token}
```

---

## 8. Pricing Plans

### Get Vehicle Pricing Plans
```http
GET /api/pricing-plans/vehicles
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Basic Vehicle Listing",
            "ad_type": "classified",
            "price": 9.99,
            "duration_days": 30,
            "description": "Basic vehicle listing for 30 days",
            "is_active": true,
            "is_featured": false
        }
    ]
}
```

---

## 9. Search & Filters

### Advanced Search
```http
POST /api/vehicles/search
```

**Request Body:**
```json
{
    "query": "Toyota Corolla",
    "filters": {
        "category_id": [1, 2],
        "make_id": 1,
        "price_range": [10000, 20000],
        "year_range": [2018, 2022],
        "advert_type": ["sale", "hire"],
        "condition": ["good", "excellent"],
        "location": {
            "country": "USA",
            "city": "New York",
            "radius": 50
        }
    },
    "sort": {
        "field": "price",
        "order": "asc"
    },
    "pagination": {
        "page": 1,
        "limit": 20
    }
}
```

### Get Search Suggestions
```http
GET /api/vehicles/suggestions?q={query}
```

---

## 10. Statistics & Reports

### Get Vehicle Statistics
```http
GET /api/vehicles/stats
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_vehicles": 1500,
        "active_vehicles": 1200,
        "featured_vehicles": 150,
        "categories": [
            {"name": "Cars", "count": 800},
            {"name": "Trucks", "count": 300},
            {"name": "Motorcycles", "count": 400}
        ],
        "price_ranges": [
            {"range": "0-5000", "count": 200},
            {"range": "5001-10000", "count": 400},
            {"range": "10001-20000", "count": 600},
            {"range": "20001+", "count": 300}
        ]
    }
}
```

### Get Popular Makes
```http
GET /api/vehicles/popular-makes
```

### Get Recent Vehicles
```http
GET /api/vehicles/recent
```

---

## Error Responses

### Standard Error Format
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "title": ["The title field is required."],
            "price": ["The price must be a number."]
        }
    }
}
```

### Common Error Codes
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (no permission)
- `404` - Not Found (resource doesn't exist)
- `422` - Validation Error (invalid input data)
- `500` - Server Error

---

## Rate Limiting
- **Public endpoints**: 100 requests per hour
- **Authenticated endpoints**: 1000 requests per hour

---

## File Upload Guidelines
- **Main image**: Max 2MB, formats: jpg, jpeg, png, webp
- **Additional images**: Max 2MB each, max 15 images
- **Supported formats**: jpg, jpeg, png, webp
- **Recommended size**: 1200x800 pixels

---

## Testing
Use the provided Postman collection or import the following JSON into Postman/Insomnia for testing all endpoints.
