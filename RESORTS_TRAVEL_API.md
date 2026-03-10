# Resorts & Travel API Documentation

## Overview
Complete Resorts & Travel marketplace system with full backend integration and admin panel management.

## 🏗️ System Architecture

### Database Tables
- `resorts_travel_categories` - Travel category management
- `resorts_travel_adverts` - Travel advertisements

### Models
- `ResortsTravel` - Main advert model with relationships
- `ResortsTravelCategory` - Category management

### Admin Resources
- `ResortsTravelResource` - Full CRUD for travel adverts
- `ResortsTravelCategoryResource` - Category management

## 🛠️ API Endpoints

### Public Endpoints

#### Travel Adverts
```
GET /api/v1/resorts-travel                    - List all travel adverts
GET /api/v1/resorts-travel/featured          - Get featured adverts
GET /api/v1/resorts-travel/advert-types     - Get advert types
GET /api/v1/resorts-travel/amenities       - Get amenities list
GET /api/v1/resorts-travel/promotion-tiers  - Get promotion options
GET /api/v1/resorts-travel/{slug}           - Get single advert
GET /api/v1/resorts-travel/categories        - Get all categories
```

#### Categories
```
GET /api/v1/resorts-travel-categories           - List categories
GET /api/v1/resorts-travel-categories/types   - Get category types
GET /api/v1/resorts-travel-categories/popular - Popular categories
GET /api/v1/resorts-travel-categories/{slug} - Single category
GET /api/v1/resorts-travel-categories/{slug}/adverts - Category adverts
```

### Authenticated Endpoints (Requires API Token)

#### Travel Adverts Management
```
POST /api/v1/resorts-travel               - Create new advert
PUT /api/v1/resorts-travel/{id}      - Update advert
DELETE /api/v1/resorts-travel/{id}    - Delete advert
GET /api/v1/resorts-travel/my-adverts - User's adverts
POST /api/v1/resorts-travel/upload-images - Upload images
POST /api/v1/resorts-travel/upload-logo   - Upload logo
```

#### Categories Management (Admin)
```
POST /api/v1/resorts-travel-categories           - Create category
PUT /api/v1/resorts-travel-categories/{id}      - Update category
DELETE /api/v1/resorts-travel-categories/{id}   - Delete category
```

## 🎯 Features Implemented

### Frontend Features
- ✅ Global travel marketplace page
- ✅ Interactive world map with region filtering
- ✅ Advanced search and filtering
- ✅ Category-based browsing
- ✅ Featured destinations carousel
- ✅ Premium advert cards with badges
- ✅ Multi-step posting form (8 steps)
- ✅ Upsell promotion system (4 tiers)
- ✅ Responsive design (Desktop/Tablet/Mobile)
- ✅ Live activity feed
- ✅ Business profiles

### Backend Features
- ✅ Complete CRUD operations
- ✅ Advanced filtering and sorting
- ✅ Image and file uploads
- ✅ Promotion tier management
- ✅ Business verification system
- ✅ Search functionality
- ✅ API authentication
- ✅ Data validation
- ✅ Soft deletes
- ✅ Relationships and eager loading

### Admin Panel Features
- ✅ Filament admin resources
- ✅ Full advert management
- ✅ Category management
- ✅ Bulk operations
- ✅ Advanced filtering
- ✅ Export capabilities
- ✅ Analytics integration
- ✅ User management integration

## 📊 Data Models

### ResortsTravel Model
```php
// Main fields include:
- user_id, category_id, title, slug, tagline
- advert_type, accommodation_type, transport_type, experience_type
- country, city, address, latitude, longitude
- price_per_night, price_per_trip, price_per_service, currency
- availability_start, availability_end
- room_types, amenities, guest_capacity
- vehicle_type, passenger_capacity, luggage_capacity
- service_area, operating_hours, airport_pickup
- duration, group_size, whats_included, what_to_bring
- description, overview, key_features, why_travellers_love_this
- nearby_attractions, additional_notes
- contact_name, business_name, phone_number, email
- website, social_links, logo, verified_business
- images, video_link, main_image
- promotion_tier, is_active, is_approximate_location
- Soft deletes support
```

### ResortsTravelCategory Model
```php
// Fields include:
- name, slug, type, description, icon, image
- is_active, sort_order
// Types: accommodation, transport, experience
```

## 🔍 Query Parameters

### Filtering Adverts
```
GET /api/v1/resorts-travel?search=dubai
GET /api/v1/resorts-travel?advert_type=accommodation
GET /api/v1/resorts-travel?country=UAE
GET /api/v1/resorts-travel?promotion_tier=featured
GET /api/v1/resorts-travel?verified_business=1
GET /api/v1/resorts-travel?price_min=100&price_max=500
GET /api/v1/resorts-travel?sort=price_low_to_high
GET /api/v1/resorts-travel?per_page=20
```

### Available Sort Options
- most_recent, most_viewed, highest_rated
- trending, price_low_to_high, price_high_to_low

## 🎨 Promotion Tiers

### Standard (Free)
- Basic listing
- Standard placement
- Contact information

### Promoted (£29.99/month)
- Highlighted listing
- Appears above standard ads
- Promoted badge
- 2× visibility boost

### Featured (£59.99/month) - *Most Popular*
- Top of category pages
- Larger advert card
- Priority search placement
- Weekly email inclusion
- Featured badge
- 4× visibility boost

### Sponsored (£99.99/month)
- Homepage placement
- Category top placement
- Homepage slider inclusion
- Social media promotion
- Sponsored badge
- Maximum visibility

### Network-Wide Boost (Custom)
- Cross-platform visibility
- Newsletter inclusion
- Push notifications
- Top Spotlight badge
- Ultimate visibility

## 🔐 Authentication

### API Authentication
All POST/PUT/DELETE endpoints require valid API token:
```php
Header: Authorization: Bearer {token}
```

### User Registration/Login
```php
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/web-login (Frontend JWT)
```

## 📱 Frontend Integration

### Pages Created
- `/resorts-travel` - Main marketplace page
- `/resorts-travel/create` - Multi-step posting form
- `/resorts-travel/{slug}` - Individual advert page

### JavaScript Features
- Interactive world map (Leaflet.js)
- Dynamic form steps
- Image preview and upload
- Real-time search
- Filter and sort functionality
- Mobile-responsive navigation

## 🚀 Deployment Notes

### Database Setup
```bash
php artisan migrate --path=database/migrations/2026_03_09_000000_create_resorts_travel_tables.php
php artisan db:seed --class=ResortsTravelSeeder
```

### Admin Access
- Navigate to `/admin` 
- Find "Marketplace" → "Resorts & Travel" in sidebar
- Full CRUD access for administrators

### API Testing
```bash
# Test categories endpoint
curl -X GET "http://your-domain.com/api/v1/resorts-travel/categories"

# Test adverts endpoint
curl -X GET "http://your-domain.com/api/v1/resorts-travel"

# Create advert (authenticated)
curl -X POST "http://your-domain.com/api/v1/resorts-travel" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Advert","advert_type":"accommodation"}'
```

## 🎯 Success Metrics

### System Capabilities
- ✅ Handles 3 advert types (Accommodation, Transport, Experience)
- ✅ Supports 11 accommodation categories
- ✅ Supports 10 transport categories  
- ✅ Supports 4 experience categories
- ✅ 4-tier promotion system
- ✅ Complete admin workflow
- ✅ Full API coverage
- ✅ Responsive frontend
- ✅ Database optimized with proper indexing
- ✅ Soft delete support
- ✅ Image and file management
- ✅ Search and filtering
- ✅ Business verification

## 📞 Support

This complete system provides:
- Full backend API with authentication
- Comprehensive admin panel integration
- Modern responsive frontend
- Database optimization and relationships
- File upload capabilities
- Promotion and monetization features
- Search and discovery functionality

The Resorts & Travel marketplace is now fully operational and ready for production use!
