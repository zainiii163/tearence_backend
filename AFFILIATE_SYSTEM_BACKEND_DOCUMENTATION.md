# Affiliate System Backend Documentation

This document outlines the complete backend implementation for the affiliate system, including API endpoints, mechanisms, database schema, and field definitions.

## Table of Contents
1. [System Overview](#system-overview)
2. [Database Schema](#database-schema)
3. [API Endpoints](#api-endpoints)
4. [Affiliate Mechanism](#affiliate-mechanism)
5. [Field Definitions](#field-definitions)
6. [Authentication & Authorization](#authentication--authorization)
7. [Error Handling](#error-handling)

---

## System Overview

The affiliate system supports two main user types:
- **Businesses**: Create affiliate programs/offers to promote their products/services
- **Promoters**: Apply to promote business offers and earn commissions

### Key Features
- Multi-step affiliate listing creation (4 steps)
- Business affiliate offers and user affiliate posts
- Application and approval workflow
- Commission tracking and analytics
- Promotional asset management
- Geographic targeting
- Traffic type restrictions

---

## Database Schema

### Core Tables

#### 1. `business_affiliate_offers`
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary Key |
| `user_id` | BIGINT UNSIGNED | Foreign Key to users (business owner) |
| `affiliate_category_id` | BIGINT UNSIGNED | Foreign Key to affiliate_categories |
| `business_name` | VARCHAR(255) | Name of the business |
| `product_service_title` | VARCHAR(255) | Title of product/service |
| `tagline` | VARCHAR(80) | Short tagline (max 80 chars) |
| `description` | TEXT | Detailed description |
| `country` | VARCHAR(255) | Target country for sales |
| `region` | VARCHAR(255) | Target region/state |
| `commission_type` | ENUM('percentage','fixed') | Commission calculation type |
| `commission_rate` | DECIMAL(8,2) | Commission value |
| `cookie_duration` | INTEGER | Cookie duration in days |
| `allowed_traffic_types` | JSON | Array of allowed traffic types |
| `restrictions` | TEXT | Promotion restrictions |
| `tracking_link` | VARCHAR(255) | Base tracking URL |
| `promotional_assets` | JSON | Array of promotional asset URLs |
| `business_email` | VARCHAR(255) | Business contact email |
| `website_url` | VARCHAR(255) | Business website URL |
| `verification_document` | VARCHAR(255) | Verification document path |
| `status` | ENUM('pending','approved','rejected','withdrawn') | Offer status |
| `is_verified` | BOOLEAN | Verification status |
| `is_promoted` | BOOLEAN | Promotion visibility |
| `is_featured` | BOOLEAN | Featured visibility |
| `is_sponsored` | BOOLEAN | Sponsored visibility |
| `price` | DECIMAL(10,2) | Listing price |
| `payment_status` | ENUM('pending','paid','failed') | Payment status |
| `expires_at` | TIMESTAMP | Offer expiration |
| `is_active` | BOOLEAN | Active status |
| `views` | INTEGER | View count |
| `clicks` | INTEGER | Click count |
| `applications` | INTEGER | Application count |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

#### 2. `affiliate_applications`
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary Key |
| `business_affiliate_offer_id` | BIGINT UNSIGNED | Foreign Key to business_affiliate_offers |
| `user_id` | BIGINT UNSIGNED | Foreign Key to users (promoter) |
| `message` | TEXT | Application message |
| `promotion_methods` | JSON | Array of promotion methods |
| `audience_details` | JSON | Audience demographics |
| `website_url` | VARCHAR(255) | Promoter website |
| `social_media_links` | JSON | Social media profile links |
| `estimated_monthly_visitors` | INTEGER | Traffic estimate |
| `status` | ENUM('pending','approved','rejected','withdrawn') | Application status |
| `reviewed_by` | BIGINT UNSIGNED | Foreign Key to users (reviewer) |
| `reviewed_at` | TIMESTAMP | Review timestamp |
| `business_responded_at` | TIMESTAMP | Business response timestamp |

#### 3. `user_affiliate_posts`
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary Key |
| `user_id` | BIGINT UNSIGNED | Foreign Key to users (promoter) |
| `affiliate_category_id` | BIGINT UNSIGNED | Foreign Key to affiliate_categories |
| `title` | VARCHAR(255) | Post title |
| `description` | TEXT | Post description |
| `country` | VARCHAR(255) | Target country |
| `region` | VARCHAR(255) | Target region |
| `affiliate_link` | VARCHAR(255) | Affiliate link URL |
| `image` | VARCHAR(255) | Post image URL |
| `hashtags` | JSON | Array of hashtags |
| `target_audience` | VARCHAR(255) | Target audience description |
| `status` | ENUM('pending','approved','rejected','withdrawn') | Post status |
| `is_promoted` | BOOLEAN | Promotion visibility |
| `is_featured` | BOOLEAN | Featured visibility |
| `is_sponsored` | BOOLEAN | Sponsored visibility |
| `is_active` | BOOLEAN | Active status |
| `views` | INTEGER | View count |
| `clicks` | INTEGER | Click count |
| `shares` | INTEGER | Share count |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

#### 4. `affiliate_categories`
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary Key |
| `name` | VARCHAR(255) | Category name |
| `slug` | VARCHAR(255) | URL slug |
| `description` | TEXT | Category description |
| `icon` | VARCHAR(255) | Icon URL |
| `is_active` | BOOLEAN | Active status |
| `sort_order` | INTEGER | Display order |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

#### 5. `affiliate_analytics`
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT UNSIGNED | Primary Key |
| `affiliatable_type` | VARCHAR(255) | Model type (polymorphic) |
| `affiliatable_id` | BIGINT UNSIGNED | Model ID (polymorphic) |
| `date` | DATE | Analytics date |
| `views` | INTEGER | Daily views |
| `unique_views` | INTEGER | Unique views |
| `clicks` | INTEGER | Daily clicks |
| `unique_clicks` | INTEGER | Unique clicks |
| `conversions` | INTEGER | Conversions |
| `revenue` | DECIMAL(10,2) | Generated revenue |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

---

## API Endpoints

### Base URL: `/api/affiliates`

### 1. Categories
#### `GET /api/affiliates/categories`
- **Description**: Get all active affiliate categories with offer counts
- **Authentication**: Optional
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "E-commerce",
      "slug": "ecommerce",
      "description": "Online retail and shopping",
      "icon": "shopping-cart",
      "active_business_offers": 25,
      "active_user_posts": 150
    }
  ]
}
```

### 2. Business Affiliate Offers

#### `GET /api/affiliates/business-offers`
- **Description**: Get all active business affiliate offers with filtering
- **Authentication**: Optional
- **Query Parameters**:
  - `category_id` (integer) - Filter by category
  - `country` (string) - Filter by country
  - `commission_type` (string) - Filter by commission type
  - `min_commission` (decimal) - Minimum commission rate
  - `max_commission` (decimal) - Maximum commission rate
  - `featured` (boolean) - Featured offers only
  - `promoted` (boolean) - Promoted offers only
  - `sponsored` (boolean) - Sponsored offers only
  - `sort` (string) - Sort field (created_at, views, clicks, commission_rate)
  - `order` (string) - Sort order (asc, desc)
  - `per_page` (integer) - Items per page
- **Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "business_name": "Tech Store",
        "product_service_title": "Premium Electronics",
        "commission_type": "percentage",
        "commission_rate": 15.00,
        "country": "United States",
        "user": {
          "name": "John Doe"
        },
        "affiliate_category": {
          "name": "Electronics"
        }
      }
    ],
    "total": 50
  }
}
```

#### `GET /api/affiliates/business-offers/{id}`
- **Description**: Get specific business affiliate offer details
- **Authentication**: Optional
- **Response**: Single offer object with full details

#### `POST /api/affiliates/business-offers`
- **Description**: Create new business affiliate offer
- **Authentication**: Required (Business User)
- **Request Body**:
```json
{
  "business_name": "Tech Store",
  "product_service_title": "Premium Electronics",
  "tagline": "Best electronics at great prices",
  "description": "We offer high-quality electronics...",
  "affiliate_category_id": 1,
  "country": "United States",
  "region": "California",
  "commission_type": "percentage",
  "commission_rate": 15.00,
  "cookie_duration": 30,
  "allowed_traffic_types": ["social_media", "email", "ppc"],
  "restrictions": "No adult content, no trademark bidding",
  "tracking_link": "https://techstore.com/affiliate",
  "promotional_assets": ["banner1.jpg", "video1.mp4"],
  "business_email": "affiliate@techstore.com",
  "website_url": "https://techstore.com",
  "verification_document": "business_license.pdf"
}
```

#### `PUT /api/affiliates/business-offers/{id}`
- **Description**: Update existing business affiliate offer
- **Authentication**: Required (Offer Owner)
- **Request Body**: Same as create, but fields are optional

#### `DELETE /api/affiliates/business-offers/{id}`
- **Description**: Delete business affiliate offer (soft delete)
- **Authentication**: Required (Offer Owner)

### 3. User Affiliate Posts

#### `GET /api/affiliates/user-posts`
- **Description**: Get all active user affiliate posts
- **Authentication**: Optional
- **Query Parameters**: Similar to business offers

#### `GET /api/affiliates/user-posts/{id}`
- **Description**: Get specific user affiliate post details
- **Authentication**: Optional

#### `POST /api/affiliates/user-posts`
- **Description**: Create new user affiliate post
- **Authentication**: Required (Promoter User)
- **Request Body**:
```json
{
  "title": "My Favorite Tech Gadgets",
  "description": "Check out these amazing tech products...",
  "affiliate_category_id": 1,
  "country": "United States",
  "affiliate_link": "https://techstore.com/affiliate/john123",
  "image": "post_image.jpg",
  "hashtags": ["tech", "gadgets", "electronics"],
  "target_audience": "Tech enthusiasts aged 25-45"
}
```

#### `PUT /api/affiliates/user-posts/{id}`
- **Description**: Update user affiliate post
- **Authentication**: Required (Post Owner)

#### `DELETE /api/affiliates/user-posts/{id}`
- **Description**: Delete user affiliate post (soft delete)
- **Authentication**: Required (Post Owner)

### 4. Applications

#### `POST /api/affiliates/business-offers/{offerId}/apply`
- **Description**: Apply to promote a business offer
- **Authentication**: Required (Promoter User)
- **Request Body**:
```json
{
  "message": "I have 5 years experience in tech promotion...",
  "promotion_methods": ["blog", "social_media", "email"],
  "audience_details": {
    "age_range": "25-45",
    "interests": ["technology", "gadgets"],
    "monthly_visitors": 10000
  },
  "website_url": "https://mytechblog.com",
  "social_media_links": {
    "twitter": "https://twitter.com/techpromoter",
    "youtube": "https://youtube.com/techpromoter"
  },
  "estimated_monthly_visitors": 10000
}
```

#### `GET /api/affiliates/my-applications`
- **Description**: Get user's affiliate applications
- **Authentication**: Required
- **Response**: Paginated list of applications

### 5. User Dashboard

#### `GET /api/affiliates/my-business-offers`
- **Description**: Get user's business affiliate offers
- **Authentication**: Required (Business User)

#### `GET /api/affiliates/my-user-posts`
- **Description**: Get user's affiliate posts
- **Authentication**: Required (Promoter User)

### 6. Tracking & Analytics

#### `POST /api/affiliates/track-click`
- **Description**: Track click on affiliate link
- **Authentication**: Optional
- **Request Body**:
```json
{
  "type": "business", // or "user"
  "id": 123
}
```

#### `GET /api/affiliates/analytics/{type}/{id}`
- **Description**: Get analytics for specific offer/post
- **Authentication**: Required (Owner or Admin)
- **Parameters**:
  - `type`: "business" or "user"
  - `id`: Offer/post ID

### 7. Search

#### `GET /api/affiliates/search`
- **Description**: Search affiliate content
- **Authentication**: Optional
- **Query Parameters**:
  - `q` (string, required) - Search query
  - `type` (string) - "all", "business", or "user"

### 8. File Upload

#### `POST /api/affiliates/upload-image`
- **Description**: Upload affiliate image
- **Authentication**: Required
- **Request**: multipart/form-data with `file` field
- **Response**:
```json
{
  "success": true,
  "message": "Image uploaded successfully",
  "data": {
    "url": "https://example.com/images/affiliate_image.jpg",
    "filename": "affiliate_image.jpg"
  }
}
```

---

## Affiliate Mechanism

### 1. Business Flow
1. **Registration**: Business creates account and verifies identity
2. **Offer Creation**: Multi-step process (4 steps):
   - Step 1: Choose path (Business/Promoter)
   - Step 2: Business information (name, email, website)
   - Step 3: Product/Service details (title, description, commission)
   - Step 4: Offer details (tracking link, restrictions, assets)
3. **Review**: Offer goes through admin review
4. **Publication**: Approved offers become visible to promoters
5. **Application Management**: Business reviews promoter applications
6. **Tracking**: Monitor clicks, conversions, and performance

### 2. Promoter Flow
1. **Discovery**: Browse available business offers
2. **Application**: Submit application with promotion methods
3. **Approval**: Wait for business approval
4. **Promotion**: Receive unique tracking links and promotional assets
5. **Tracking**: Monitor clicks, conversions, and earnings
6. **Payout**: Receive commissions based on performance

### 3. Commission Tracking
- **Cookie Duration**: Configurable tracking period (default 30 days)
- **Traffic Types**: Allowed promotion methods (social, email, PPC, etc.)
- **Conversion Tracking**: Track sales/sign-ups from affiliate links
- **Commission Calculation**: 
  - Percentage: `sale_amount * commission_rate / 100`
  - Fixed: Fixed amount per conversion

### 4. Analytics System
- **Daily Aggregation**: Views, clicks, conversions tracked daily
- **Unique Tracking**: Distinguish unique vs total interactions
- **Revenue Calculation**: Track generated revenue per offer
- **Performance Metrics**: CTR, conversion rate, EPC

---

## Field Definitions

### Business Affiliate Offer Fields

#### Business Information
- **business_name** (string, 255, required): Legal business name
- **business_email** (email, required): Contact email for affiliate matters
- **website_url** (url, optional): Official business website
- **country** (string, 255, required): Primary target country
- **region** (string, 255, optional): State/province/region

#### Product/Service Details
- **product_service_title** (string, 255, required): Product or service name
- **tagline** (string, 80, optional): Catchy marketing tagline
- **description** (text, required): Detailed description of offer
- **affiliate_category_id** (integer, required): Category classification

#### Commission Structure
- **commission_type** (enum, required): "percentage" or "fixed"
- **commission_rate** (decimal, required): Commission value
- **cookie_duration** (integer, required): Tracking cookie days

#### Promotion Settings
- **allowed_traffic_types** (array, optional): Permitted promotion methods
  - social_media: Facebook, Instagram, Twitter, etc.
  - email: Email marketing campaigns
  - ppc: Pay-per-click advertising
  - blogging: Content marketing/blogs
  - influencer: Influencer marketing
  - other: Other methods
- **restrictions** (text, optional): Promotion restrictions and guidelines
- **tracking_link** (url, required): Base tracking URL for affiliates
- **promotional_assets** (array, optional): Marketing materials (banners, logos, etc.)

#### Verification & Status
- **verification_document** (string, optional): Business verification file
- **status** (enum): pending/approved/rejected/withdrawn
- **is_verified** (boolean): Admin verification status
- **is_active** (boolean): Offer visibility status

#### Visibility Options
- **is_promoted** (boolean): Enhanced visibility
- **is_featured** (boolean): Featured placement
- **is_sponsored** (boolean): Sponsored listing

#### Analytics
- **views** (integer): Total page views
- **clicks** (integer): Total link clicks
- **applications** (integer): Total applications received

### User Affiliate Post Fields

#### Basic Information
- **title** (string, 255, required): Post headline
- **description** (text, required): Post content
- **affiliate_category_id** (integer, required): Category classification
- **country** (string, 255, optional): Target country
- **region** (string, 255, optional): Target region

#### Promotion Details
- **affiliate_link** (url, required): Affiliate link to promote
- **image** (string, required): Post image URL
- **hashtags** (array, optional): Social media hashtags
- **target_audience** (string, 255, optional): Target audience description

#### Analytics
- **views** (integer): Total post views
- **clicks** (integer): Total link clicks
- **shares** (integer): Social media shares

### Application Fields

#### Application Details
- **message** (text, optional): Personal message to business
- **promotion_methods** (array, optional): Planned promotion methods
- **audience_details** (array, optional): Audience demographics
- **website_url** (url, optional): Promoter website
- **social_media_links** (array, optional): Social media profiles
- **estimated_monthly_visitors** (integer, optional): Traffic estimate

#### Status Tracking
- **status** (enum): pending/approved/rejected/withdrawn
- **reviewed_by** (integer): Admin reviewer ID
- **reviewed_at** (timestamp): Review timestamp
- **business_responded_at** (timestamp): Business response timestamp

---

## Authentication & Authorization

### Authentication Methods
- **Sanctum Tokens**: API token authentication
- **JWT Tokens**: JSON Web Token support
- **Session Auth**: Web-based authentication

### User Roles
- **Business User**: Can create/manage affiliate offers
- **Promoter User**: Can create posts and apply to offers
- **Admin User**: Can manage all content and users

### Permissions
- **Own Content**: Users can only edit/delete their own content
- **Application Access**: Businesses can view applications for their offers
- **Admin Override**: Admins can manage any content

---

## Error Handling

### Standard Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes
- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not Found
- **422**: Validation Error
- **500**: Server Error

### Common Error Scenarios
1. **Validation Errors**: Missing or invalid required fields
2. **Authorization Errors**: User lacks required permissions
3. **Not Found**: Requested resource doesn't exist
4. **Duplicate Content**: User already applied to offer
5. **File Upload Errors**: Invalid file type or size

---

## Rate Limiting

### API Limits
- **Guest Users**: 100 requests/hour
- **Authenticated Users**: 1000 requests/hour
- **Premium Users**: 5000 requests/hour

### Sensitive Endpoints
- **Create Operations**: 10 requests/minute
- **File Upload**: 5 requests/minute
- **Search**: 60 requests/minute

---

## Webhooks

### Supported Events
- **offer.created**: New business offer created
- **offer.approved**: Offer approved by admin
- **application.submitted**: New application received
- **application.approved**: Application approved
- **conversion.tracked**: New conversion recorded

### Webhook Format
```json
{
  "event": "offer.created",
  "data": {
    "id": 123,
    "business_name": "Tech Store",
    "created_at": "2026-04-02T12:00:00Z"
  },
  "timestamp": "2026-04-02T12:00:00Z"
}
```

---

## SDK Integration

### JavaScript SDK
```javascript
// Initialize SDK
const affiliate = new AffiliateAPI('your-api-key');

// Get offers
const offers = await affiliate.getBusinessOffers({
  category_id: 1,
  country: 'US'
});

// Track click
await affiliate.trackClick('business', 123);
```

### PHP SDK
```php
// Initialize SDK
$affiliate = new AffiliateAPI('your-api-key');

// Create offer
$offer = $affiliate->createBusinessOffer([
  'business_name' => 'Tech Store',
  'commission_rate' => 15.00
]);
```

---

## Testing

### Test Environment
- **URL**: `https://api-test.example.com`
- **Authentication**: Test tokens available
- **Data**: Reset daily

### Test Cases
1. **CRUD Operations**: Create, read, update, delete all entities
2. **Authentication**: Valid/invalid token scenarios
3. **Validation**: All field validation rules
4. **Permissions**: Role-based access control
5. **Performance**: Load testing for high-traffic endpoints

---

## Deployment

### Environment Variables
```env
AFFILIATE_API_URL=https://api.example.com
AFFILIATE_UPLOAD_PATH=/var/www/uploads/affiliate
AFFILIATE_MAX_FILE_SIZE=2048
AFFILIATE_COOKIE_DURATION=30
AFFILIATE_COMMISSION_MIN=0.01
AFFILIATE_COMMISSION_MAX=100
```

### Database Migrations
```bash
php artisan migrate
php artisan db:seed --class=AffiliateSeeder
```

### Queue Configuration
```env
QUEUE_CONNECTION=redis
QUEUE_NAME=affiliate_analytics
```

---

## Monitoring & Logging

### Key Metrics
- **API Response Time**: Average response time per endpoint
- **Error Rate**: Percentage of failed requests
- **User Activity**: Active users and content creation
- **Conversion Tracking**: Affiliate conversion rates

### Logging Levels
- **INFO**: Successful operations, user actions
- **WARNING**: Validation errors, authorization failures
- **ERROR**: System errors, database failures
- **CRITICAL**: Security issues, service downtime

---

## Security Considerations

### Data Protection
- **PII Encryption**: Sensitive user data encrypted at rest
- **API Key Security**: Rotatable API keys with expiration
- **Input Sanitization**: All user inputs sanitized and validated
- **SQL Injection Prevention**: Using parameterized queries

### Access Control
- **Rate Limiting**: Prevent API abuse
- **IP Whitelisting**: Optional IP restrictions
- **CORS Configuration**: Proper cross-origin settings
- **HTTPS Only**: Enforce SSL/TLS encryption

---

## Performance Optimization

### Caching Strategy
- **Redis Caching**: Frequently accessed data cached
- **CDN Integration**: Static assets served via CDN
- **Database Indexing**: Optimized queries with proper indexes
- **Lazy Loading**: Related data loaded on demand

### Scalability
- **Horizontal Scaling**: Load balancer with multiple app servers
- **Database Sharding**: Separate read/write databases
- **Queue Processing**: Background jobs for analytics processing
- **CDN Distribution**: Global content delivery

---

## Support & Maintenance

### Support Channels
- **Email**: affiliate-support@example.com
- **Documentation**: https://docs.example.com/affiliate
- **Status Page**: https://status.example.com
- **GitHub Issues**: https://github.com/example/affiliate-api

### Maintenance Schedule
- **Weekly**: Security patches and updates
- **Monthly**: Performance optimization
- **Quarterly**: Feature releases and upgrades
- **Annually**: Architecture review and planning

---

*This documentation covers the complete affiliate system backend implementation. For specific implementation details or additional features, please refer to the API reference or contact the development team.*
