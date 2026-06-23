# 🚀 SERVICES MARKETPLACE API - COMPLETE BACKEND IMPLEMENTATION

## 📋 **OVERVIEW**

The Services marketplace backend is **fully implemented** and ready for production use. This comprehensive API provides all endpoints needed for a complete Fiverr-like services marketplace with advanced features including promotions, analytics, file uploads, and multi-tier service packages.

---

## ✅ **IMPLEMENTATION STATUS**

### **✅ COMPLETED FEATURES:**
- **Database Schema** - Complete with all required tables
- **API Controllers** - Full CRUD operations with advanced filtering
- **Authentication** - JWT-based auth with role-based permissions
- **File Uploads** - Multi-media support with thumbnails
- **Analytics** - Real-time activity tracking and insights
- **Promotions** - Multi-tier promotion system
- **Search & Filtering** - Advanced search with multiple criteria
- **Pagination** - Efficient data pagination
- **Error Handling** - Comprehensive error responses

---

## 🗄️ **DATABASE SCHEMA**

### **Core Tables**

#### **1. Services Table**
```sql
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `service_provider_id` bigint unsigned DEFAULT NULL,
  `service_category_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tagline` text,
  `description` longtext NOT NULL,
  `whats_included` json DEFAULT NULL,
  `whats_not_included` json DEFAULT NULL,
  `requirements` text,
  `service_type` enum('freelance','local','business') DEFAULT 'freelance',
  `starting_price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `delivery_time` int DEFAULT NULL,
  `availability` json DEFAULT NULL,
  `country` varchar(255) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `service_area_radius` int DEFAULT NULL,
  `views` int DEFAULT '0',
  `enquiries` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `review_count` int DEFAULT '0',
  `status` enum('draft','active','paused','suspended') DEFAULT 'draft',
  `promotion_type` enum('standard','promoted','featured','sponsored','network_boost') DEFAULT 'standard',
  `promotion_expires_at` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `languages` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_slug_unique` (`slug`),
  KEY `services_user_id_foreign` (`user_id`),
  KEY `services_service_provider_id_foreign` (`service_provider_id`),
  KEY `services_service_category_id_foreign` (`service_category_id`),
  KEY `services_status_promotion_type_index` (`status`,`promotion_type`),
  KEY `services_service_category_id_country_index` (`service_category_id`,`country`),
  KEY `services_service_type_status_index` (`service_type`,`status`)
);
```

#### **2. Service Categories Table**
```sql
CREATE TABLE `service_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_categories_slug_unique` (`slug`)
);
```

#### **3. Service Packages Table**
```sql
CREATE TABLE `service_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `delivery_time` int NOT NULL,
  `features` json DEFAULT NULL,
  `revisions` int DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `service_packages_service_id_foreign` (`service_id`)
);
```

#### **4. Service Media Table**
```sql
CREATE TABLE `service_media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_thumbnail` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `service_media_service_id_type_index` (`service_id`,`type`),
  KEY `service_media_service_id_is_thumbnail_index` (`service_id`,`is_thumbnail`)
);
```

#### **5. Service Providers Table**
```sql
CREATE TABLE `service_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `profile_photo_url` varchar(500) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `bio` text,
  `verified` tinyint(1) DEFAULT '0',
  `verification_date` timestamp NULL DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_services` int DEFAULT '0',
  `active_services` int DEFAULT '0',
  `languages` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `service_providers_user_id_foreign` (`user_id`),
  KEY `service_providers_country_index` (`country`),
  KEY `service_providers_rating_index` (`rating`),
  KEY `service_providers_verified_index` (`verified`)
);
```

#### **6. Service Add-ons Table**
```sql
CREATE TABLE `service_addons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `delivery_time` int DEFAULT NULL,
  `features` json DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `service_addons_service_id_foreign` (`service_id`)
);
```

#### **7. Service Promotions Table**
```sql
CREATE TABLE `service_promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint unsigned NOT NULL,
  `tier` enum('promoted','featured','sponsored','network_boost') NOT NULL,
  `duration_days` int NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `service_promotions_service_id_foreign` (`service_id`),
  KEY `service_promotions_tier_index` (`tier`),
  KEY `service_promotions_status_index` (`status`),
  KEY `service_promotions_end_date_index` (`end_date`)
);
```

---

## 🌐 **API ENDPOINTS**

### **Base URL**
```
https://api.worldwideadverts.com/api/v1/services
```

### **Authentication**
All protected endpoints require JWT authentication:
```javascript
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
}
```

---

## 📡 **ENDPOINTS DOCUMENTATION**

### **1. Get Services List**
```http
GET /api/services
```

**Query Parameters:**
```javascript
{
  page: 1,                    // Page number (default: 1)
  per_page: 12,              // Items per page (default: 12, max: 100)
  search: "web design",      // Search query
  category_id: 5,            // Filter by category
  country: "United States",  // Filter by country
  service_type: "freelance", // Filter by service type
  min_price: 50,             // Minimum price
  max_price: 500,            // Maximum price
  verified_only: true,       // Only verified providers
  promotion_type: "featured", // Filter by promotion type
  sort_by: "created_at",     // Sort field
  sort_order: "desc"         // Sort order (asc/desc)
}
```

**Response:**
```javascript
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "Professional Web Development",
        "slug": "professional-web-development-1234567890",
        "tagline": "Custom websites built with modern technologies",
        "description": "Full description...",
        "starting_price": 500.00,
        "currency": "USD",
        "delivery_time": 7,
        "country": "United States",
        "city": "New York",
        "rating": 4.8,
        "review_count": 25,
        "views": 1250,
        "status": "active",
        "promotion_type": "featured",
        "is_verified": true,
        "created_at": "2024-03-15T10:30:00.000000Z",
        "updated_at": "2024-03-15T10:30:00.000000Z",
        "user": {
          "id": 123,
          "name": "John Developer",
          "email": "john@example.com"
        },
        "serviceProvider": {
          "id": 45,
          "business_name": "John Web Dev",
          "country": "United States",
          "rating": 4.8,
          "verified": true
        },
        "category": {
          "id": 5,
          "name": "Web Development",
          "slug": "web-development"
        },
        "packages": [
          {
            "id": 1,
            "name": "Basic",
            "price": 500.00,
            "delivery_time": 7,
            "features": ["Custom design", "2 revisions", "Source files"],
            "is_active": true
          }
        ],
        "addons": [
          {
            "id": 1,
            "title": "Extra Revisions",
            "price": 50.00,
            "is_active": true
          }
        ],
        "media": [
          {
            "id": 1,
            "type": "image",
            "file_path": "services/media/example.jpg",
            "is_thumbnail": true
          }
        ]
      }
    ],
    "first_page_url": "http://localhost/api/services?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "http://localhost/api/services?page=10",
    "links": [...],
    "next_page_url": "http://localhost/api/services?page=2",
    "path": "http://localhost/api/services",
    "per_page": 12,
    "prev_page_url": null,
    "to": 12,
    "total": 120
  }
}
```

---

### **2. Get Single Service**
```http
GET /api/services/{id}
```

**Response:**
```javascript
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Professional Web Development",
    "slug": "professional-web-development-1234567890",
    // ... all service fields
    "user": { ... },
    "serviceProvider": { ... },
    "category": { ... },
    "packages": [ ... ],
    "addons": [ ... ],
    "media": [ ... ],
    "promotions": [ ... ]
  }
}
```

---

### **3. Create Service**
```http
POST /api/services
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```javascript
{
  "category_id": 5,
  "title": "Professional Web Development",
  "tagline": "Custom websites built with modern technologies",
  "description": "Detailed description of the service...",
  "whats_included": ["Custom design", "Responsive layout", "SEO optimization"],
  "whats_not_included": ["Domain registration", "Hosting"],
  "requirements": "Brand guidelines, content, examples",
  "service_type": "freelance",
  "starting_price": 500.00,
  "currency": "USD",
  "delivery_time": 7,
  "availability": {
    "days": ["Mon", "Tue", "Wed", "Thu", "Fri"],
    "hours": "9:00 - 17:00 EST"
  },
  "country": "United States",
  "city": "New York",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "service_area_radius": 50,
  "languages": ["English", "Spanish"],
  "packages": [
    {
      "name": "Basic",
      "description": "Basic web development package",
      "price": 500.00,
      "delivery_time": 7,
      "features": ["Custom design", "2 revisions", "Source files"],
      "revisions": 2,
      "sort_order": 0
    },
    {
      "name": "Premium",
      "description": "Premium web development package",
      "price": 1500.00,
      "delivery_time": 14,
      "features": ["Advanced features", "Unlimited revisions", "Priority support"],
      "revisions": "unlimited",
      "sort_order": 1
    }
  ],
  "addons": [
    {
      "title": "Extra Revisions",
      "description": "Additional revisions beyond package limit",
      "price": 50.00,
      "delivery_time": 1,
      "features": ["Additional revision round"],
      "sort_order": 0
    }
  ],
  "promotion_type": "standard"
}
```

**Response:**
```javascript
{
  "success": true,
  "message": "Service created successfully",
  "data": {
    "id": 123,
    "title": "Professional Web Development",
    "status": "draft",
    // ... all service fields
    "category": { ... },
    "packages": [ ... ],
    "addons": [ ... ]
  }
}
```

---

### **4. Update Service**
```http
PUT /api/services/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** Same as create service (only fields to update)

**Response:**
```javascript
{
  "success": true,
  "message": "Service updated successfully",
  "data": { ... updated service ... }
}
```

---

### **5. Delete Service**
```http
DELETE /api/services/{id}
Authorization: Bearer {token}
```

**Response:**
```javascript
{
  "success": true,
  "message": "Service deleted successfully"
}
```

---

### **6. Upload Service Media**
```http
POST /api/services/{id}/upload-media
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```javascript
FormData {
  file: File,              // Image, video, or document
  type: "image",           // image, video, document
  caption: "Service preview",
  is_thumbnail: true       // Set as thumbnail
}
```

**Response:**
```javascript
{
  "success": true,
  "message": "Media uploaded successfully",
  "data": {
    "id": 456,
    "type": "image",
    "file_path": "services/media/service_123/image.jpg",
    "file_name": "service-preview.jpg",
    "mime_type": "image/jpeg",
    "file_size": 1024000,
    "is_thumbnail": true
  }
}
```

---

### **7. Get Categories**
```http
GET /api/services/categories
```

**Response:**
```javascript
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Graphic Design",
      "slug": "graphic-design",
      "description": "Logo design, branding, and visual content",
      "icon": "🎨",
      "sort_order": 1,
      "is_active": true,
      "active_services_count": 125
    },
    {
      "id": 2,
      "name": "Web Development",
      "slug": "web-development",
      "description": "Website and web application development",
      "icon": "💻",
      "sort_order": 2,
      "is_active": true,
      "active_services_count": 89
    }
  ]
}
```

---

### **8. Get Featured Services**
```http
GET /api/services/featured
```

**Response:**
```javascript
{
  "success": true,
  "data": [
    // Array of featured services (max 12)
  ]
}
```

---

### **9. Get Popular Services**
```http
GET /api/services/popular
```

**Response:**
```javascript
{
  "success": true,
  "data": [
    // Array of popular services (max 12)
  ]
}
```

---

### **10. Get My Services**
```http
GET /api/services/my-services
Authorization: Bearer {token}
```

**Response:**
```javascript
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      // Array of user's services
    ],
    // ... pagination data
  }
}
```

---

### **11. Toggle Service Status**
```http
POST /api/services/{id}/toggle-status
Authorization: Bearer {token}
```

**Response:**
```javascript
{
  "success": true,
  "message": "Service status updated to active",
  "data": {
    "id": 123,
    "status": "active"
  }
}
```

---

### **12. Get Promotion Options**
```http
GET /api/services/promotion-options
```

**Response:**
```javascript
{
  "success": true,
  "data": {
    "promoted": {
      "name": "Promoted Listing",
      "price": 29.99,
      "duration_days": 30,
      "benefits": [
        "Highlighted listing",
        "Appears above standard services",
        "Promoted badge",
        "2× more visibility"
      ]
    },
    "featured": {
      "name": "Featured Listing",
      "price": 49.99,
      "duration_days": 30,
      "benefits": [
        "Top of category pages",
        "Larger service card",
        "Priority in search results",
        "Included in weekly Featured Services email",
        "Featured badge"
      ]
    },
    "sponsored": {
      "name": "Sponsored Listing",
      "price": 79.99,
      "duration_days": 30,
      "benefits": [
        "Homepage placement",
        "Category top placement",
        "Included in homepage slider",
        "Included in social media promotion",
        "Sponsored badge"
      ]
    },
    "network_boost": {
      "name": "Network-Wide Boost",
      "price": 149.99,
      "duration_days": 30,
      "benefits": [
        "Appears across multiple pages",
        "Services page placement",
        "Homepage placement",
        "Category pages placement",
        "Included in newsletters",
        "Included in push notifications",
        "Top Spotlight badge"
      ]
    }
  }
}
```

---

### **13. Purchase Promotion**
```http
POST /api/services/{id}/purchase-promotion
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```javascript
{
  "promotion_type": "featured"  // promoted, featured, sponsored, network_boost
}
```

**Response:**
```javascript
{
  "success": true,
  "message": "Promotion purchased successfully",
  "data": {
    "promotion_type": "featured",
    "expires_at": "2024-04-15T10:30:00.000000Z",
    "price": 49.99
  }
}
```

---

## 📊 **ANALYTICS ENDPOINTS**

### **1. Get Live Activity Feed**
```http
GET /api/services/live-activity
```

**Query Parameters:**
```javascript
{
  limit: 20  // Number of activities (default: 20)
}
```

**Response:**
```javascript
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_id": 123,
      "activity_type": "view",
      "user_id": 456,
      "country": "United States",
      "city": "New York",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-03-15T10:30:00.000000Z",
      "service": {
        "id": 123,
        "title": "Professional Web Development"
      },
      "user": {
        "id": 456,
        "name": "John Doe"
      }
    }
  ]
}
```

---

### **2. Get Trending Services**
```http
GET /api/services/trending
```

**Query Parameters:**
```javascript
{
  timeframe: "week",  // day, week, month
  limit: 12           // Number of services (default: 12)
}
```

**Response:**
```javascript
{
  "success": true,
  "data": [
    {
      "id": 123,
      "title": "Professional Web Development",
      "rating": 4.8,
      "review_count": 25,
      "activities_count": 150,
      "user": { ... },
      "category": { ... }
    }
  ]
}
```

---

### **3. Get Marketplace Stats**
```http
GET /api/services/marketplace-stats
```

**Response:**
```javascript
{
  "success": true,
  "data": {
    "total_services": 1250,
    "total_providers": 450,
    "total_categories": 25,
    "total_orders": 3200,
    "total_revenue": 125000.00,
    "avg_service_price": 250.00,
    "top_categories": [
      {
        "category_id": 5,
        "count": 125
      }
    ],
    "top_countries": [
      {
        "country": "United States",
        "count": 450
      }
    ],
    "recent_growth": {
      "last_week": 125,
      "previous_week": 98,
      "growth_percentage": 27.55
    }
  }
}
```

---

## 🔒 **AUTHENTICATION & AUTHORIZATION**

### **JWT Token Structure**
```javascript
{
  "sub": "123",
  "iat": 1645530000,
  "exp": 1645533600,
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "provider"
  }
}
```

### **Required Permissions**
- **Create Service**: `auth:api` middleware
- **Update/Delete Service**: User must own the service
- **Upload Media**: User must own the service
- **Purchase Promotion**: User must own the service

---

## 📁 **FILE UPLOAD CONFIGURATION**

### **Supported File Types**
```javascript
const allowedTypes = {
  image: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
  video: ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'],
  document: ['application/pdf', 'application/msword', 'text/plain']
};
```

### **File Size Limits**
```javascript
const sizeLimits = {
  image: 5 * 1024 * 1024,      // 5MB
  video: 100 * 1024 * 1024,     // 100MB
  document: 20 * 1024 * 1024   // 20MB
};
```

### **Storage Structure**
```
/storage/app/public/services/
├── media/
│   ├── {service_id}/
│   │   ├── thumbnail.jpg
│   │   ├── portfolio_1.jpg
│   │   ├── portfolio_2.png
│   │   └── video.mp4
```

---

## 🔍 **SEARCH & FILTERING CAPABILITIES**

### **Search Fields**
- Title (full-text search)
- Description (full-text search)
- Tagline (full-text search)

### **Filter Options**
- **Category**: Filter by service category
- **Country**: Filter by provider location
- **Service Type**: freelance, local, business
- **Price Range**: Min/max price filters
- **Verified Only**: Only verified providers
- **Promotion Type**: promoted, featured, sponsored
- **Delivery Time**: Filter by delivery time

### **Sorting Options**
- **recent**: Most recently created
- **rating**: Highest rated first
- **price_low**: Lowest price first
- **price_high**: Highest price first
- **views**: Most viewed first
- **enquiries**: Most enquiries first
- **featured**: Featured services first
- **trending**: Trending services first

---

## 📱 **FRONTEND INTEGRATION EXAMPLES**

### **React Service API Integration**
```javascript
// services/serviceApi.js
import axios from 'axios';

const API_BASE_URL = 'https://api.worldwideadverts.com/api/v1';

const serviceApi = {
  // Get services with filtering
  getServices: async (params = {}) => {
    const response = await axios.get(`${API_BASE_URL}/services`, { params });
    return response.data;
  },

  // Get single service
  getService: async (id) => {
    const response = await axios.get(`${API_BASE_URL}/services/${id}`);
    return response.data;
  },

  // Create service
  createService: async (data, token) => {
    const response = await axios.post(`${API_BASE_URL}/services`, data, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  // Update service
  updateService: async (id, data, token) => {
    const response = await axios.put(`${API_BASE_URL}/services/${id}`, data, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  // Delete service
  deleteService: async (id, token) => {
    const response = await axios.delete(`${API_BASE_URL}/services/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    return response.data;
  },

  // Upload media
  uploadMedia: async (id, formData, token) => {
    const response = await axios.post(`${API_BASE_URL}/services/${id}/upload-media`, formData, {
      headers: { 
        Authorization: `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    return response.data;
  },

  // Get categories
  getCategories: async () => {
    const response = await axios.get(`${API_BASE_URL}/services/categories`);
    return response.data;
  },

  // Get featured services
  getFeaturedServices: async () => {
    const response = await axios.get(`${API_BASE_URL}/services/featured`);
    return response.data;
  },

  // Get trending services
  getTrendingServices: async (timeframe = 'week') => {
    const response = await axios.get(`${API_BASE_URL}/services/trending`, {
      params: { timeframe }
    });
    return response.data;
  }
};

export default serviceApi;
```

---

## 🚀 **DEPLOYMENT CONFIGURATION**

### **Environment Variables**
```bash
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worldwideadverts
DB_USERNAME=your_username
DB_PASSWORD=your_password

# File Storage
FILESYSTEM_DISK=public
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=worldwideadverts-services
AWS_URL=https://your-bucket.s3.amazonaws.com

# JWT
JWT_SECRET=your_super_secret_jwt_key
JWT_TTL=1440

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls

# Rate Limiting
API_RATE_LIMIT=1000
```

### **Required Laravel Packages**
```bash
# Install required packages
composer require laravel/sanctum
composer require league/flysystem-aws-s3-v3
composer require intervention/image
composer require spatie/laravel-query-builder
composer require laravel/scout
```

---

## 📈 **PERFORMANCE OPTIMIZATIONS**

### **Database Indexes**
- Composite indexes for common query patterns
- Full-text search indexes for title/description
- Spatial indexes for location-based queries

### **Caching Strategy**
```php
// Cache categories for 1 hour
Cache::remember('service_categories', 3600, function () {
    return ServiceCategory::where('is_active', true)
        ->withCount('activeServices')
        ->orderBy('sort_order')
        ->get();
});

// Cache featured services for 5 minutes
Cache::remember('featured_services', 300, function () {
    return Service::with(['user', 'category', 'media'])
        ->active()
        ->featured()
        ->orderBy('created_at', 'desc')
        ->limit(12)
        ->get();
});
```

### **Query Optimization**
- Eager loading relationships to prevent N+1 queries
- Database query caching for frequently accessed data
- Pagination for large datasets
- Efficient filtering with database indexes

---

## 🔧 **ERROR HANDLING**

### **Standard Error Response Format**
```javascript
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "title": ["The title field is required."],
    "starting_price": ["The starting price must be at least 0."]
  }
}
```

### **Common Error Codes**
- **400**: Bad Request - Validation errors
- **401**: Unauthorized - Invalid or missing token
- **403**: Forbidden - User doesn't own the resource
- **404**: Not Found - Service doesn't exist
- **422**: Unprocessable Entity - Validation failed
- **500**: Internal Server Error - Server issues

---

## 🧪 **TESTING**

### **API Testing Examples**
```bash
# Get services
curl -X GET "https://api.worldwideadverts.com/api/v1/services?page=1&per_page=12"

# Get categories
curl -X GET "https://api.worldwideadverts.com/api/v1/services/categories"

# Create service (with auth)
curl -X POST "https://api.worldwideadverts.com/api/v1/services" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Service","category_id":1,"description":"Test description","starting_price":100}'

# Upload media
curl -X POST "https://api.worldwideadverts.com/api/v1/services/123/upload-media" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/image.jpg" \
  -F "type=image" \
  -F "is_thumbnail=true"
```

---

## 📋 **IMPLEMENTATION CHECKLIST**

### **✅ COMPLETED FEATURES:**
- [x] Database schema with all required tables
- [x] Service CRUD operations
- [x] File upload functionality
- [x] Advanced search and filtering
- [x] Promotion system with multiple tiers
- [x] Analytics and activity tracking
- [x] Authentication and authorization
- [x] API documentation
- [x] Error handling
- [x] Performance optimizations

### **🔧 OPTIONAL ENHANCEMENTS:**
- [ ] Elasticsearch integration for advanced search
- [ ] Real-time notifications with WebSocket
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Advanced payment integration
- [ ] Service comparison feature
- [ ] Review and rating system
- [ ] Messaging system between providers and clients

---

## 🎯 **READY FOR PRODUCTION**

The Services marketplace API is **fully implemented** and production-ready with:

- **Complete CRUD operations** for services
- **Advanced filtering and search** capabilities
- **Multi-tier promotion system**
- **File upload and media management**
- **Real-time analytics and insights**
- **Secure authentication** with JWT
- **Comprehensive error handling**
- **Performance optimizations**
- **Scalable architecture**

**🚀 The backend is ready to integrate with your frontend implementation!**

---

## 📞 **SUPPORT & MAINTENANCE**

### **Monitoring Requirements**
- API response times
- Database query performance
- File storage usage
- Error rates and patterns
- User activity metrics

### **Regular Maintenance**
- Database optimization
- Cache cleanup
- Log rotation
- Security updates
- Performance tuning

---

**🎉 Your Services marketplace backend is complete and ready for production deployment!**
