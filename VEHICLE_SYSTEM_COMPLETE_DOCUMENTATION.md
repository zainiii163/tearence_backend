# Vehicle System Complete Documentation

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [API Endpoints](#api-endpoints)
4. [Database Schema](#database-schema)
5. [Field Definitions](#field-definitions)
6. [Admin Panel Features](#admin-panel-features)
7. [Error Handling](#error-handling)
8. [File Upload Guidelines](#file-upload-guidelines)
9. [Rate Limiting](#rate-limiting)
10. [Examples](#examples)

---

## Overview

The Vehicle System is a comprehensive vehicle marketplace backend that supports multiple vehicle types (cars, trucks, motorcycles, commercial vehicles) with features for listing, searching, analytics, and user management.

### Key Features
- Multi-category vehicle listings (Cars, Trucks, Motorcycles, etc.)
- Advanced search and filtering
- User authentication and authorization
- Image and video uploads
- Analytics tracking (views, clicks, saves, enquiries)
- Pricing plans system
- Favourites/bookmarks system
- Enquiry system
- Featured/promoted/sponsored listings
- Location-based search
- Admin panel for content management

---

## Authentication

### Base URL
```
http://127.0.0.1:8000/api
```

### Bearer Token Authentication
All admin/management endpoints require Bearer Token authentication:

```http
Authorization: Bearer {token}
```

### Login Endpoint
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600,
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        }
    }
}
```

---

## API Endpoints

### 1. Vehicle Categories Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/vehicle-categories` | Get all categories | No |
| GET | `/api/vehicle-categories/active` | Get active categories only | No |
| GET | `/api/vehicle-categories/{id}` | Get category details | No |
| POST | `/api/vehicle-categories` | Create new category | Yes |
| PUT | `/api/vehicle-categories/{id}` | Update category | Yes |
| DELETE | `/api/vehicle-categories/{id}` | Delete category | Yes |
| POST | `/api/vehicle-categories/{id}/toggle-status` | Toggle category status | Yes |

**Category Response Structure:**
```json
{
    "id": 1,
    "name": "Cars",
    "slug": "cars",
    "is_active": true,
    "created_at": "2026-03-28T07:00:00.000000Z",
    "updated_at": "2026-03-28T07:00:00.000000Z"
}
```

### 2. Vehicle Makes & Models

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/vehicle-makes` | Get all makes | No |
| GET | `/api/vehicle-makes/active` | Get active makes only | No |
| GET | `/api/vehicle-makes/{id}/models` | Get models by make | No |
| GET | `/api/vehicle-models/{makeId}` | Get models by make (alternative) | No |

**Make Response Structure:**
```json
{
    "id": 1,
    "name": "Toyota",
    "slug": "toyota",
    "country": "Japan",
    "is_active": true,
    "models_count": 10,
    "created_at": "2026-03-28T07:00:00.000000Z",
    "updated_at": "2026-03-28T07:00:00.000000Z"
}
```

**Model Response Structure:**
```json
{
    "id": 1,
    "name": "Corolla",
    "slug": "corolla",
    "make_id": 1,
    "year_start": 2020,
    "year_end": null,
    "category": "sedan",
    "is_active": true,
    "created_at": "2026-03-28T07:00:00.000000Z",
    "updated_at": "2026-03-28T07:00:00.000000Z"
}
```

### 3. Vehicle Listings Management

#### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/vehicles` | Get all vehicles (with filters) |
| GET | `/api/vehicles/featured` | Get featured vehicles |
| GET | `/api/vehicles/promoted` | Get promoted vehicles |
| GET | `/api/vehicles/sponsored` | Get sponsored vehicles |
| GET | `/api/vehicles/recent` | Get recent vehicles |
| GET | `/api/vehicles/stats` | Get vehicle statistics |
| GET | `/api/vehicles/{id}` | Get vehicle details |
| GET | `/api/vehicles/{id}/related` | Get related vehicles |
| GET | `/api/vehicles/popular-makes` | Get popular makes |

#### Authenticated Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/vehicles` | Create vehicle |
| PUT | `/api/vehicles/{id}` | Update vehicle |
| DELETE | `/api/vehicles/{id}` | Delete vehicle |
| GET | `/api/vehicles/my-vehicles` | Get my vehicles |
| POST | `/api/vehicles/{id}/toggle-status` | Toggle vehicle status |
| POST | `/api/vehicles/{id}/mark-sold` | Mark as sold |

#### Analytics & Interaction

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/vehicles/{id}/view` | Increment views |
| POST | `/api/vehicles/{id}/click` | Increment clicks |
| GET | `/api/vehicles/{id}/analytics` | Get vehicle analytics |

#### Favourites System

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/vehicles/{id}/favourite/check` | Check if favourited | No |
| POST | `/api/vehicles/{id}/favourite` | Toggle favourite | Yes |
| GET | `/api/vehicles/saved` | Get saved vehicles | Yes |
| POST | `/api/vehicles/{id}/save` | Save/unsave vehicle | Yes |

#### Enquiries System

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/vehicles/{id}/enquiry` | Submit enquiry |
| GET | `/api/vehicles/{id}/enquiries` | Get vehicle enquiries (owner) |
| GET | `/api/enquiries/my` | Get my enquiries |

### 4. Pricing Plans

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/pricing-plans/vehicles` | Get vehicle pricing plans |

---

## Database Schema

### Vehicles Table Structure

```sql
CREATE TABLE vehicles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    business_id BIGINT NULL,
    category_id BIGINT,
    make_id BIGINT,
    model_id BIGINT,
    title VARCHAR(255),
    tagline VARCHAR(255) NULL,
    description TEXT,
    advert_type ENUM('sale', 'hire', 'lease', 'transport_service'),
    condition ENUM('new', 'used', 'excellent', 'good', 'fair'),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    year INT,
    mileage INT NULL,
    fuel_type VARCHAR(50),
    transmission VARCHAR(50),
    engine_size VARCHAR(20) NULL,
    color VARCHAR(50),
    doors INT NULL,
    seats INT NULL,
    body_type VARCHAR(50),
    vin VARCHAR(17) NULL,
    registration_number VARCHAR(50) NULL,
    
    -- Commercial vehicle fields
    payload_capacity DECIMAL(10,2) NULL,
    axles INT NULL,
    emission_class VARCHAR(20) NULL,
    length DECIMAL(10,2) NULL,
    engine_type VARCHAR(50) NULL,
    capacity DECIMAL(10,2) NULL,
    trailer_included BOOLEAN DEFAULT FALSE,
    service_area VARCHAR(255) NULL,
    operating_hours VARCHAR(255) NULL,
    passenger_capacity INT NULL,
    luggage_capacity INT NULL,
    airport_pickup BOOLEAN DEFAULT FALSE,
    
    -- Pricing
    price DECIMAL(12,2),
    price_type ENUM('fixed', 'per_day', 'per_week', 'per_month', 'per_hour'),
    negotiable BOOLEAN DEFAULT FALSE,
    deposit DECIMAL(12,2) NULL,
    pricing_plan_id BIGINT NULL,
    
    -- Location
    country VARCHAR(100),
    city VARCHAR(100),
    address TEXT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    show_exact_location BOOLEAN DEFAULT TRUE,
    
    -- Contact
    contact_name VARCHAR(255),
    contact_phone VARCHAR(50),
    contact_email VARCHAR(255),
    website VARCHAR(255) NULL,
    
    -- Features & History
    features JSON NULL,
    service_history TEXT NULL,
    mot_expiry DATE NULL,
    road_tax_status VARCHAR(50) NULL,
    previous_owners INT NULL,
    
    -- Media
    main_image VARCHAR(255) NULL,
    additional_images JSON NULL,
    video_link VARCHAR(500) NULL,
    
    -- System fields
    is_active BOOLEAN DEFAULT TRUE,
    is_promoted BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_sponsored BOOLEAN DEFAULT FALSE,
    is_top_of_category BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    clicks INT DEFAULT 0,
    saves INT DEFAULT 0,
    enquiries INT DEFAULT 0,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Field Definitions

### Basic Information Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | BIGINT | Auto | Unique identifier |
| `title` | VARCHAR(255) | Yes | Vehicle title |
| `tagline` | VARCHAR(255) | No | Vehicle tagline/slogan |
| `description` | TEXT | Yes | Detailed description |
| `advert_type` | ENUM | Yes | Type: "sale", "hire", "lease", "transport_service" |
| `condition` | ENUM | Yes | Condition: "new", "used", "excellent", "good", "fair" |
| `status` | ENUM | Auto | Listing status: "pending", "approved", "rejected" |
| `year` | INT | Yes | Manufacturing year |

### Vehicle Specification Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `mileage` | INT | No | Odometer reading |
| `fuel_type` | VARCHAR(50) | No | Fuel type: "petrol", "diesel", "electric", "hybrid" |
| `transmission` | VARCHAR(50) | No | Transmission: "manual", "automatic", "cvt" |
| `engine_size` | VARCHAR(20) | No | Engine size (e.g., "1.8L") |
| `color` | VARCHAR(50) | No | Vehicle color |
| `doors` | INT | No | Number of doors |
| `seats` | INT | No | Number of seats |
| `body_type` | VARCHAR(50) | No | Body type: "sedan", "hatchback", "suv", "truck" |
| `vin` | VARCHAR(17) | No | Vehicle Identification Number |
| `registration_number` | VARCHAR(50) | No | License plate number |

### Commercial Vehicle Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `payload_capacity` | DECIMAL(10,2) | No | Payload capacity (for trucks) |
| `axles` | INT | No | Number of axles |
| `emission_class` | VARCHAR(20) | No | Emission class |
| `length` | DECIMAL(10,2) | No | Vehicle length |
| `engine_type` | VARCHAR(50) | No | Engine type |
| `capacity` | DECIMAL(10,2) | No | Capacity (passenger/cargo) |
| `trailer_included` | BOOLEAN | No | Trailer included |
| `service_area` | VARCHAR(255) | No | Service area for transport |
| `operating_hours` | VARCHAR(255) | No | Operating hours |
| `passenger_capacity` | INT | No | Passenger capacity |
| `luggage_capacity` | INT | No | Luggage capacity |
| `airport_pickup` | BOOLEAN | No | Airport pickup available |

### Pricing Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `price` | DECIMAL(12,2) | Yes | Vehicle price |
| `price_type` | ENUM | Yes | Price type: "fixed", "per_day", "per_week", "per_month", "per_hour" |
| `negotiable` | BOOLEAN | No | Price negotiable |
| `deposit` | DECIMAL(12,2) | No | Deposit amount |
| `pricing_plan_id` | BIGINT | No | Associated pricing plan |

### Location Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `country` | VARCHAR(100) | Yes | Country |
| `city` | VARCHAR(100) | Yes | City |
| `address` | TEXT | No | Full address |
| `latitude` | DECIMAL(10,8) | No | GPS latitude |
| `longitude` | DECIMAL(11,8) | No | GPS longitude |
| `show_exact_location` | BOOLEAN | No | Show exact location |

### Contact Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `contact_name` | VARCHAR(255) | Yes | Contact person name |
| `contact_phone` | VARCHAR(50) | Yes | Contact phone |
| `contact_email` | VARCHAR(255) | Yes | Contact email |
| `website` | VARCHAR(255) | No | Website URL |

### Features & History Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `features` | JSON | No | Array of features |
| `service_history` | TEXT | No | Service history description |
| `mot_expiry` | DATE | No | MOT expiry date |
| `road_tax_status` | VARCHAR(50) | No | Road tax status |
| `previous_owners` | INT | No | Number of previous owners |

### Media Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `main_image` | VARCHAR(255) | No | Main image filename |
| `additional_images` | JSON | No | Array of additional images |
| `video_link` | VARCHAR(500) | No | Video URL (YouTube, etc.) |

### System Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | BIGINT | Yes | Owner user ID |
| `business_id` | BIGINT | No | Business ID (if applicable) |
| `category_id` | BIGINT | Yes | Vehicle category ID |
| `make_id` | BIGINT | Yes | Vehicle make ID |
| `model_id` | BIGINT | Yes | Vehicle model ID |
| `is_active` | BOOLEAN | Auto | Listing active |
| `is_promoted` | BOOLEAN | Auto | Promoted listing |
| `is_featured` | BOOLEAN | Auto | Featured listing |
| `is_sponsored` | BOOLEAN | Auto | Sponsored listing |
| `is_top_of_category` | BOOLEAN | Auto | Top of category |
| `views` | INT | Auto | View count |
| `clicks` | INT | Auto | Click count |
| `saves` | INT | Auto | Save count |
| `enquiries` | INT | Auto | Enquiry count |
| `expires_at` | DATETIME | Auto | Listing expiry date |

---

## Search & Filtering Parameters

### Basic Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | INT | Page number (default: 1) | `page=2` |
| `limit` | INT | Items per page (default: 10) | `limit=20` |
| `search` | STRING | Search in title, description | `search=Toyota Corolla` |

### Category & Model Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category_id` | INT | Filter by category | `category_id=1` |
| `make_id` | INT | Filter by make | `make_id=1` |
| `model_id` | INT | Filter by model | `model_id=1` |

### Vehicle Specific Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `advert_type` | ENUM | Filter by advert type | `advert_type=sale` |
| `condition` | ENUM | Filter by condition | `condition=good` |
| `fuel_type` | STRING | Filter by fuel type | `fuel_type=petrol` |
| `transmission` | STRING | Filter by transmission | `transmission=automatic` |
| `year_min` | INT | Minimum year | `year_min=2018` |
| `year_max` | INT | Maximum year | `year_max=2022` |
| `price_min` | DECIMAL | Minimum price | `price_min=5000` |
| `price_max` | DECIMAL | Maximum price | `price_max=20000` |
| `mileage_max` | INT | Maximum mileage | `mileage_max=50000` |

### Location Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `country` | STRING | Filter by country | `country=USA` |
| `city` | STRING | Filter by city | `city=New York` |

### Special Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `featured` | BOOLEAN | Show featured only | `featured=true` |
| `promoted` | BOOLEAN | Show promoted only | `promoted=true` |
| `sponsored` | BOOLEAN | Show sponsored only | `sponsored=true` |
| `active` | BOOLEAN | Show active only | `active=true` |

### Sorting Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `sort_by` | STRING | Sort field | `sort_by=price` |
| `sort_order` | STRING | Sort order | `sort_order=asc` |

**Sort By Options:** `created_at`, `price`, `year`, `views`, `saves`, `clicks`
**Sort Order Options:** `asc`, `desc`

---

## Admin Panel Features

### Vehicle Management

#### Create Vehicle
```http
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

#### Update Vehicle
```http
PUT /api/vehicles/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

#### Delete Vehicle
```http
DELETE /api/vehicles/{id}
Authorization: Bearer {token}
```

#### Approve/Reject Listings
```http
POST /api/vehicles/{id}/toggle-status
Authorization: Bearer {token}
```

#### Mark as Sold
```http
POST /api/vehicles/{id}/mark-sold
Authorization: Bearer {token}
```

### User Management

#### View User Vehicles
```http
GET /api/vehicles/my-vehicles
Authorization: Bearer {token}
```

#### Manage User Permissions
- View user activity
- Track user listings
- Manage user access

### Analytics & Reporting

#### Vehicle Statistics
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

#### Vehicle Analytics
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

### Content Moderation

#### Review Pending Listings
- Filter by status: `pending`
- Approve/reject functionality
- Bulk operations support

#### Manage Inappropriate Content
- Report handling
- Content removal
- User warnings

### Pricing Management

#### View Pricing Plans
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

## Error Handling

### Standard Error Response Format
```json
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable error message",
        "details": {
            "field_name": ["Specific error messages"]
        }
    }
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `UNAUTHORIZED` | 401 | Invalid or missing authentication token |
| `FORBIDDEN` | 403 | User does not have permission |
| `NOT_FOUND` | 404 | Resource does not exist |
| `VALIDATION_ERROR` | 422 | Input validation failed |
| `SERVER_ERROR` | 500 | Internal server error |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |

### Validation Error Examples
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "title": ["The title field is required."],
            "price": ["The price must be a number."],
            "email": ["The email must be a valid email address."]
        }
    }
}
```

---

## File Upload Guidelines

### Image Specifications

| Requirement | Specification |
|-------------|---------------|
| **Max file size** | 2MB per image |
| **Supported formats** | jpg, jpeg, png, webp |
| **Recommended dimensions** | 1200x800 pixels |
| **Max additional images** | 15 images |
| **Storage location** | `/storage/vehicles/` |

### Upload Format
Use `multipart/form-data` for file uploads:

```http
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: multipart/form-data

--boundary
Content-Disposition: form-data; name="title"
Toyota Corolla 2020
--boundary
Content-Disposition: form-data; name="price"
15000
--boundary
Content-Disposition: form-data; name="main_image"; filename="vehicle.jpg"
Content-Type: image/jpeg
[image data]
--boundary
Content-Disposition: form-data; name="additional_images[]"; filename="interior.jpg"
Content-Type: image/jpeg
[image data]
--boundary--
```

### File Upload JavaScript Example
```javascript
const createVehicle = async (vehicleData, token) => {
    const formData = new FormData();
    
    // Add all text fields
    Object.keys(vehicleData).forEach(key => {
        if (key !== 'main_image' && key !== 'additional_images') {
            formData.append(key, vehicleData[key]);
        }
    });
    
    // Add main image
    if (vehicleData.main_image) {
        formData.append('main_image', vehicleData.main_image);
    }
    
    // Add additional images
    if (vehicleData.additional_images) {
        vehicleData.additional_images.forEach(image => {
            formData.append('additional_images[]', image);
        });
    }

    const response = await fetch('/api/vehicles', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        body: formData
    });
    
    return response.json();
};
```

---

## Rate Limiting

### Rate Limits by Endpoint Type

| Endpoint Type | Requests per Hour |
|---------------|-------------------|
| **Public endpoints** | 100 requests/hour |
| **Authenticated endpoints** | 1000 requests/hour |
| **File uploads** | 10 requests/minute |
| **Search endpoints** | 200 requests/hour |

### Rate Limit Headers
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

### Rate Limit Error Response
```json
{
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests. Please try again later.",
        "retry_after": 3600
    }
}
```

---

## Examples

### Example 1: Create Vehicle Listing

#### Request
```http
POST /api/vehicles
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: multipart/form-data

category_id: 1
make_id: 1
model_id: 1
title: Toyota Corolla 2020
tagline: Well maintained family car
description: Excellent condition Toyota Corolla, regularly serviced...
advert_type: sale
condition: good
year: 2020
mileage: 45000
fuel_type: petrol
transmission: automatic
engine_size: 1.8L
color: White
doors: 4
seats: 5
body_type: sedan
vin: 12345678901234567
registration_number: ABC-123
price: 15000.00
price_type: fixed
negotiable: false
country: USA
city: New York
address: 123 Main St
latitude: 40.71280000
longitude: -74.00600000
show_exact_location: true
contact_name: John Doe
contact_phone: +1234567890
contact_email: john@example.com
features: ["Air Conditioning", "Power Steering", "ABS"]
service_history: Full service history available
mot_expiry: 2024-12-31
road_tax_status: Valid
previous_owners: 2
pricing_plan_id: 1
main_image: [file]
additional_images[]: [file]
additional_images[]: [file]
```

#### Response
```json
{
    "success": true,
    "data": {
        "id": 123,
        "title": "Toyota Corolla 2020",
        "tagline": "Well maintained family car",
        "description": "Excellent condition Toyota Corolla...",
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
        "features": ["Air Conditioning", "Power Steering", "ABS"],
        "service_history": "Full service history available",
        "mot_expiry": "2024-12-31",
        "road_tax_status": "Valid",
        "previous_owners": 2,
        "status": "pending",
        "is_active": true,
        "is_promoted": false,
        "is_featured": false,
        "is_sponsored": false,
        "is_top_of_category": false,
        "views": 0,
        "clicks": 0,
        "saves": 0,
        "enquiries": 0,
        "main_image": "http://127.0.0.1:8000/storage/vehicles/image1.jpg",
        "additional_images": [
            "http://127.0.0.1:8000/storage/vehicles/image2.jpg",
            "http://127.0.0.1:8000/storage/vehicles/image3.jpg"
        ],
        "video_link": null,
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
}
```

### Example 2: Search Vehicles with Filters

#### Request
```http
GET /api/vehicles?category_id=1&make_id=1&advert_type=sale&condition=good&price_min=10000&price_max=20000&year_min=2018&country=USA&sort_by=price&sort_order=asc&page=1&limit=20
```

#### Response
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "title": "Toyota Corolla 2020",
            "price": 15000.00,
            "year": 2020,
            "mileage": 45000,
            "condition": "good",
            "advert_type": "sale",
            "main_image": "http://127.0.0.1:8000/storage/vehicles/image1.jpg",
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
            "location": "New York, USA",
            "created_at": "2026-03-28T07:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8,
        "from": 1,
        "to": 20
    }
}
```

### Example 3: Submit Vehicle Enquiry

#### Request
```http
POST /api/vehicles/123/enquiry
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: application/json

{
    "message": "Is this vehicle still available? I'm interested in viewing it this weekend.",
    "contact_phone": "+1987654321",
    "contact_email": "buyer@example.com"
}
```

#### Response
```json
{
    "success": true,
    "data": {
        "id": 456,
        "vehicle_id": 123,
        "user_id": 789,
        "message": "Is this vehicle still available? I'm interested in viewing it this weekend.",
        "contact_phone": "+1987654321",
        "contact_email": "buyer@example.com",
        "status": "pending",
        "created_at": "2026-03-28T07:00:00.000000Z"
    },
    "message": "Enquiry submitted successfully"
}
```

### Example 4: Toggle Favourite

#### Request
```http
POST /api/vehicles/123/favourite
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

#### Response (Added to favourites)
```json
{
    "success": true,
    "message": "Vehicle added to favourites",
    "is_favourited": true,
    "total_favourites": 25
}
```

#### Response (Removed from favourites)
```json
{
    "success": true,
    "message": "Vehicle removed from favourites",
    "is_favourited": false,
    "total_favourites": 24
}
```

---

## Testing

### Testing with Postman
1. Import the `VEHICLES_API_COLLECTION.json` file into Postman
2. Set environment variables:
   - `base_url`: `http://127.0.0.1:8000/api`
   - `auth_token`: Your JWT token
3. Test endpoints using the provided collection

### Testing with cURL

#### Get All Vehicles
```bash
curl -X GET "http://127.0.0.1:8000/api/vehicles?page=1&limit=10"
```

#### Get Vehicle Details
```bash
curl -X GET "http://127.0.0.1:8000/api/vehicles/123"
```

#### Create Vehicle
```bash
curl -X POST "http://127.0.0.1:8000/api/vehicles" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Vehicle",
    "category_id": 1,
    "make_id": 1,
    "model_id": 1,
    "price": 15000,
    "advert_type": "sale",
    "condition": "good",
    "year": 2020,
    "country": "USA",
    "city": "New York",
    "contact_name": "Test User",
    "contact_phone": "+1234567890",
    "contact_email": "test@example.com"
  }'
```

---

## Support

For API issues or questions:
1. Check the error messages and logs
2. Test with the Postman collection
3. Verify authentication tokens are valid
4. Ensure proper request format and headers
5. Check rate limiting headers if receiving 429 errors

### Common Issues
- **401 Unauthorized**: Check your JWT token is valid and not expired
- **422 Validation Error**: Ensure all required fields are present and valid
- **429 Rate Limit**: Wait before making more requests
- **404 Not Found**: Verify the endpoint URL is correct
- **500 Server Error**: Check server logs for detailed error information

---

*This documentation covers the complete Vehicle System API including all endpoints, field definitions, admin features, and usage examples.*
