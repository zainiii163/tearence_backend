# Buy & Sell Marketplace API Documentation

## Overview

The Buy & Sell Marketplace system provides a comprehensive platform for users to buy, sell, and trade items with real-time data, analytics, and user interactions. This document outlines all API endpoints, data structures, and integration patterns.

## Base Configuration

### Environment Variables
```bash
REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_BUYSELL_API_URL=http://localhost:8000/api/v1/buysell
```

### Authentication
All API endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer <jwt_token>
```

## API Endpoints

### 1. Buy & Sell Adverts API

#### Get All Adverts
```http
GET /api/v1/buysell?category=1&subcategory=2&condition=new&price_min=10&price_max=1000&country=USA&city=New+York&search=keyword&sort_by=created_at&sort_order=desc&page=1&limit=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "iPhone 13 Pro Max",
      "slug": "iphone-13-pro-max",
      "description": "Brand new iPhone 13 Pro Max, 256GB, Pacific Blue",
      "price": 1099.99,
      "currency": "USD",
      "condition": "new",
      "category": {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics",
        "icon": "💻"
      },
      "subcategory": {
        "id": 1,
        "name": "Smartphones",
        "slug": "smartphones"
      },
      "location": {
        "country": "USA",
        "city": "New York",
        "address": "123 Main St",
        "postal_code": "10001",
        "latitude": 40.7128,
        "longitude": -74.0060
      },
      "contact": {
        "phone": "+1-555-123-4567",
        "email": "seller@example.com",
        "whatsapp": "+1-555-123-4567",
        "preferred_contact": "phone"
      },
      "media": {
        "images": [
          {
            "id": 1,
            "url": "https://example.com/storage/buysell-images/image1.jpg",
            "thumbnail_url": "https://example.com/storage/buysell-thumbnails/thumb1.jpg",
            "alt_text": "iPhone 13 Pro Max",
            "sort_order": 0
          }
        ],
        "videos": []
      },
      "promotion": {
        "is_featured": false,
        "is_urgent": true,
        "is_promoted": false,
        "promotion_plan": null,
        "expires_at": "2024-04-15T23:59:59Z"
      },
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1-555-123-4567",
        "verified": true,
        "rating": 4.8,
        "total_sales": 15
      },
      "stats": {
        "views_count": 234,
        "favorites_count": 12,
        "enquiries_count": 5,
        "shares_count": 3
      },
      "status": "active",
      "is_active": true,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z",
      "expires_at": "2024-04-15T23:59:59Z"
    }
  ],
  "meta": {
    "total": 1,
    "count": 1,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

#### Get Featured Adverts
```http
GET /api/v1/buysell/featured?limit=10
```

#### Get Recent Adverts
```http
GET /api/v1/buysell/recent?limit=10
```

#### Get Advert by Slug
```http
GET /api/v1/buysell/{slug}
```

#### Search Adverts
```http
GET /api/v1/buysell/search?q=iphone&category=1&price_min=100&price_max=1000
```

#### Create Advert
```http
POST /api/v1/buysell
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "title": "iPhone 13 Pro Max",
  "description": "Brand new iPhone 13 Pro Max, 256GB, Pacific Blue",
  "price": 1099.99,
  "currency": "USD",
  "category_id": 1,
  "subcategory_id": 1,
  "condition": "new",
  "country": "USA",
  "city": "New York",
  "address": "123 Main St",
  "postal_code": "10001",
  "phone": "+1-555-123-4567",
  "email": "seller@example.com",
  "whatsapp": "+1-555-123-4567",
  "preferred_contact": "phone",
  "images": ["image1.jpg", "image2.jpg"],
  "promotion_plan_id": null
}
```

#### Update Advert
```http
PUT /api/v1/buysell/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "title": "Updated iPhone 13 Pro Max",
  "price": 999.99
}
```

#### Delete Advert
```http
DELETE /api/v1/buysell/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Get User's Adverts
```http
GET /api/v1/buysell/my-adverts?page=1&limit=20
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Save/Unsave Advert
```http
POST /api/v1/buysell/{id}/save
Content-Type: application/json
Authorization: Bearer <jwt_token>

DELETE /api/v1/buysell/{id}/unsave
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Get Saved Adverts
```http
GET /api/v1/buysell/saved-adverts?page=1&limit=20
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Contact Seller
```http
POST /api/v1/buysell/{id}/contact
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "message": "Is this item still available?",
  "phone": "+1-555-987-6543",
  "email": "buyer@example.com"
}
```

#### Get Advert Analytics
```http
GET /api/v1/buysell/{id}/analytics
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Report Advert
```http
POST /api/v1/buysell/{id}/report
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "reason": "spam",
  "description": "This appears to be a fraudulent listing"
}
```

### 2. Buy & Sell Categories API

#### Get All Categories
```http
GET /api/v1/buysell-categories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Phones, laptops, TVs, and other electronic devices",
      "icon": "💻",
      "image": null,
      "level": 1,
      "parent_id": null,
      "is_active": true,
      "sort_order": 1,
      "advert_count": 1247,
      "children": [
        {
          "id": 2,
          "name": "Smartphones",
          "slug": "smartphones",
          "level": 2,
          "parent_id": 1
        }
      ]
    }
  ]
}
```

#### Get Featured Categories
```http
GET /api/v1/buysell-categories/featured
```

#### Get Popular Categories
```http
GET /api/v1/buysell-categories/popular
```

#### Get Category Tree
```http
GET /api/v1/buysell-categories/tree
```

#### Get Category by Slug
```http
GET /api/v1/buysell-categories/{slug}
```

#### Get Category Adverts
```http
GET /api/v1/buysell-categories/{slug}/adverts?page=1&limit=20
```

#### Get Subcategories
```http
GET /api/v1/buysell-categories/{id}/subcategories
```

### 3. Buy & Sell Promotions API

#### Get Promotion Plans
```http
GET /api/v1/buysell-promotions/plans
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Basic",
      "slug": "basic",
      "description": "Standard listing with basic visibility",
      "price": 0.00,
      "duration_days": 90,
      "features": [
        "Standard listing",
        "90 days duration",
        "Basic search visibility",
        "Image uploads (up to 5)"
      ],
      "visibility_multiplier": 1.0,
      "is_active": true,
      "sort_order": 1
    },
    {
      "id": 2,
      "name": "Promoted",
      "slug": "promoted",
      "description": "Enhanced visibility with promotional features",
      "price": 19.99,
      "duration_days": 30,
      "features": [
        "Promoted badge",
        "Higher search ranking",
        "30 days duration",
        "Image uploads (up to 10)",
        "Highlighted in search results",
        "Social media promotion"
      ],
      "visibility_multiplier": 2.0,
      "is_active": true,
      "sort_order": 2
    }
  ]
}
```

#### Purchase Promotion
```http
POST /api/v1/buysell-promotions/purchase
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "advert_id": 1,
  "promotion_plan_id": 2,
  "payment_method": "stripe"
}
```

#### Get User's Promotions
```http
GET /api/v1/buysell-promotions/my-promotions
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Extend Promotion
```http
POST /api/v1/buysell-promotions/{id}/extend
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "days": 7
}
```

#### Cancel Promotion
```http
DELETE /api/v1/buysell-promotions/{id}/cancel
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

### 4. Buy & Sell Upload API

#### Upload Images
```http
POST /api/v1/buysell-upload/images
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "images": [<file>, <file>]
}
```

#### Upload Single Image
```http
POST /api/v1/buysell-upload/image
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "image": <file>
}
```

#### Upload Video
```http
POST /api/v1/buysell-upload/video
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "video": <file>,
  "thumbnail": <file>
}
```

#### Delete File
```http
DELETE /api/v1/buysell-upload/file
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "filename": "buysell_image.jpg",
  "type": "image"
}
```

## Data Models

### BuySellAdvert
```typescript
interface BuySellAdvert {
  id: number;
  title: string;
  slug: string;
  description: string;
  price: number;
  currency: string;
  condition: 'new' | 'like_new' | 'good' | 'fair' | 'poor';
  category: BuySellCategory;
  subcategory?: BuySellCategory;
  location: {
    country: string;
    city?: string;
    address?: string;
    postal_code?: string;
    latitude?: number;
    longitude?: number;
  };
  contact: {
    phone?: string;
    email?: string;
    whatsapp?: string;
    preferred_contact?: 'phone' | 'email' | 'whatsapp';
  };
  media: {
    images: BuySellImage[];
    videos: BuySellVideo[];
  };
  promotion: {
    is_featured: boolean;
    is_urgent: boolean;
    is_promoted: boolean;
    promotion_plan?: PromotionPlan;
    expires_at?: string;
  };
  user?: {
    id: number;
    name: string;
    email: string;
    phone?: string;
    verified: boolean;
    rating: number;
    total_sales: number;
  };
  stats: {
    views_count: number;
    favorites_count: number;
    enquiries_count: number;
    shares_count: number;
  };
  status: 'active' | 'sold' | 'expired' | 'draft';
  is_active: boolean;
  created_at: string;
  updated_at: string;
  expires_at?: string;
}
```

### BuySellCategory
```typescript
interface BuySellCategory {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
  image?: string;
  level: number;
  parent_id?: number;
  is_active: boolean;
  sort_order: number;
  advert_count?: number;
  children?: BuySellCategory[];
  parent?: BuySellCategory;
}
```

### PromotionPlan
```typescript
interface PromotionPlan {
  id: number;
  name: string;
  slug: string;
  description: string;
  price: number;
  duration_days: number;
  features: string[];
  visibility_multiplier: number;
  is_active: boolean;
  sort_order: number;
}
```

## Error Handling

### Standard Error Response Format
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "title": ["The title field is required."],
      "price": ["The price must be at least 0."]
    }
  }
}
```

### HTTP Status Codes
- `200 OK` - Success
- `201 Created` - Resource created successfully
- `400 Bad Request` - Validation error
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

## Integration Examples

### React Hook Usage
```javascript
import { useBuySellAdverts, useMyAdverts, useSavedAdverts } from '../hooks/useBuySellData';

// In component
const { data: adverts, loading, error, refetch } = useBuySellAdverts({
  category: selectedCategory,
  condition: selectedCondition,
  price_min: minPrice,
  price_max: maxPrice,
  search: searchQuery,
  sort_by: sortBy,
  sort_order: sortOrder,
  page: currentPage,
  limit: itemsPerPage
});

// Handle save advert
const handleSaveAdvert = async (advertId) => {
  try {
    await buySellApi.saveAdvert(advertId);
    // Update UI to show saved status
  } catch (error) {
    console.error('Failed to save advert:', error);
  }
};
```

### File Upload Example
```javascript
const handleImageUpload = async (files) => {
  const formData = new FormData();
  files.forEach((file, index) => {
    formData.append(`images[${index}]`, file);
  });

  try {
    const response = await buySellUploadApi.uploadImages(formData);
    // Handle successful upload
    setUploadedImages(response.data);
  } catch (error) {
    console.error('Upload failed:', error);
    // Handle error
  }
};
```

## Rate Limiting

- **File Upload**: 10 images per request, 5MB per image, 50MB for videos
- **API Calls**: 100 requests per minute per user
- **Advert Creation**: 10 requests per minute per user
- **Contact Requests**: 20 requests per minute per user

## Security Considerations

1. **Input Validation**: All user inputs must be validated on both client and server
2. **File Upload Security**: 
   - File type restrictions (images: jpg, png, gif, webp; videos: mp4, webm, mov, avi)
   - File size limits (images: 5MB; videos: 50MB)
   - Virus scanning for all uploads
3. **SQL Injection**: Use parameterized queries
4. **XSS Protection**: Sanitize all user-generated content
5. **CSRF Protection**: Use CSRF tokens for state-changing operations

## Testing

### Postman Collection
A comprehensive Postman collection is available with all API endpoints for testing:
- `WWA_BuySell_API.postman_collection.json`

### Environment Setup
```bash
# Development
REACT_APP_API_URL=http://localhost:8000/api/v1

# Production
REACT_APP_API_URL=https://api.worldwideadverts.com/api/v1
```

## Frontend Integration Checklist

- [ ] JWT token management implemented
- [ ] API interceptors configured
- [ ] Error handling implemented
- [ ] Loading states implemented
- [ ] File upload handling implemented
- [ ] Image/video preview functionality
- [ ] Save/unsave functionality
- [ ] Contact seller functionality
- [ ] Analytics dashboard
- [ ] Promotion purchase flow
- [ ] Responsive design maintained
- [ ] Accessibility features implemented

This documentation provides a complete reference for implementing and integrating the Buy & Sell Marketplace API system.

## Backend Implementation Summary

### Database Structure
- **buysell_adverts table**: Complete table with all required fields including item details, location, contact info, media, analytics, and promotion settings
- **buysell_categories table**: Hierarchical category management with parent-child relationships
- **buysell_promotions table**: Promotion tracking with plans and expiry dates
- **buysell_saved_adverts table**: User saved/bookmarked adverts tracking
- **buysell_advert_views table**: View tracking with IP and user agent
- **buysell_favorites table**: Favorite/bookmark tracking
- **buysell_enquiries table**: Contact requests and messages
- **buysell_images table**: Image management with thumbnails
- **buysell_videos table**: Video management with thumbnails

### Models Created
- **BuySellAdvert**: Main model with relationships, scopes, and methods for filtering, searching, and analytics
- **BuySellCategory**: Category model with hierarchical relationships and advert counting
- **BuySellPromotionPlan**: Promotion pricing tiers with features and duration
- **BuySellSavedAdvert**: User saved adverts tracking
- **BuySellAdvertView**: View tracking model
- **BuySellFavorite**: Favorite tracking model
- **BuySellEnquiry**: Contact enquiry model
- **BuySellImage**: Image management model
- **BuySellVideo**: Video management model

### API Controllers
- **BuySellController**: Full CRUD operations with filtering, sorting, search, and analytics
- **BuySellCategoryController**: Category management with hierarchical data
- **BuySellItemController**: Alternative API for item management
- **BuySellPromotionController**: Promotion purchase and management
- **BuySellUploadController**: File upload handling for images and videos

### Filament Admin Resources
- **BuySellAdvertResource**: Complete admin resource with full CRUD operations
  - Comprehensive form with all advert fields
  - Advanced filtering and search capabilities
  - Tables with sorting and bulk actions
  - Status badges and promotion indicators
  - Image previews and file uploads

- **BuySellPromotionPlanResource**: Admin resource for managing promotion tiers
  - Plan configuration with features
  - Pricing and duration management
  - Active/inactive toggles

### Frontend User Dashboard
- **BuySellDashboardController**: Complete frontend controller
  - Dashboard with statistics and analytics
  - My adverts management with filtering
  - Advert creation and editing
  - Saved adverts management
  - Analytics tracking and reporting
  - File upload integration

### Frontend Views
- **Dashboard**: User statistics overview
  - Statistics cards (total adverts, active, sold, expired, views, favorites, enquiries)
  - Recent adverts list
  - Category breakdown
  - Quick action buttons

- **Create Form**: Comprehensive advert submission
  - Multi-step form sections
  - Category/subcategory selection
  - Image upload capabilities
  - Promotion tier selection
  - Terms agreement
  - AJAX form submission

- **My Adverts**: User advert management
  - Search and filtering
  - Status management
  - Analytics access
  - Edit and delete actions

- **Browse Page**: Public advert browsing
  - Search and filtering
  - Featured adverts section
  - Category grid
  - Real-time API integration

- **Analytics**: Advert analytics dashboard
  - Views over time chart
  - Device breakdown
  - Location analytics
  - Engagement metrics

- **Saved Adverts**: User saved items management
  - Saved items list
  - Quick actions
  - Unsave functionality

### Frontend Routes
- **Public routes**: Browse, show, category filtering
- **Authenticated routes**: Dashboard, create, edit, analytics, saved adverts
- **Route protection**: Proper middleware for authenticated routes

### Key Features Implemented

#### Admin Panel:
- Complete advert management with all metadata
- Visual promotion status indicators
- Advanced filtering and search
- Bulk operations support
- Analytics and statistics widgets
- Promotion plan management
- File upload handling

#### Frontend:
- User dashboard with comprehensive statistics
- Advert submission forms with image uploads
- Browse and search functionality
- Promotion tier selection
- Analytics tracking and reporting
- Saved items management
- Responsive mobile design

#### API Integration:
- RESTful API design with proper HTTP status codes
- Comprehensive validation for all inputs
- File upload security and storage management
- Database relationships and foreign key constraints
- Query scopes for efficient filtering
- JSON response formatting
- Authentication middleware protection

#### Advanced Features:
- Hierarchical category system
- Multi-tier promotion system (Basic, Promoted, Featured, Sponsored, Urgent, Weekend Special)
- Image and video upload with thumbnails
- View and favorite tracking with analytics
- Contact seller functionality
- Location-based search with coordinates
- Real-time search and filtering
- Saved items management

### Seeders Created
- **BuySellCategorySeeder**: Populates hierarchical categories with 13 main categories and subcategories
- **BuySellPromotionPlanSeeder**: Populates promotion pricing tiers with different features and durations
- Updated DatabaseSeeder to include new seeders

The system is now ready for deployment with a database connection and provides a complete backend solution for the Buy & Sell Marketplace frontend. All forms are visible in the admin panel and user submissions are properly tracked and manageable.
