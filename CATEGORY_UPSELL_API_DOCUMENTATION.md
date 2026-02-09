# WWA API Documentation - Categories & Upselling System

## Overview

This document covers the updated category structure and upselling system implementation for the WWA platform.

---

## 📂 Updated Categories

### Category Structure Changes

#### 1. **Buy and Sell** (Previously "Items")
- **Description**: General selling posts - items for sale, swap, or free
- **Parent Category**: Yes
- **Subcategories**:
  - Items for Sale
  - Items for Swap  
  - Free Items

#### 2. **Hotel, Resorts & Travel** (Combined Category)
- **Description**: Combined category for hotels, B&B, transport services, and tourist activities
- **Parent Category**: Yes
- **Subcategories**:
  - Hotels
  - B&B
  - Transport Services
  - Tour Services

#### 3. **Property & Real Estate**
- **Description**: Property listings - houses, commercial properties for sale or rent
- **Parent Category**: Yes
- **Subcategories**:
  - Houses
  - Commercial
  - Industrial
  - Farm
  - Plots

#### 4. **Books** (Enhanced with Filters)
- **Description**: Books for sale, PDF downloads, and audiobooks with genre filtering
- **Parent Category**: Yes
- **Subcategories**:
  - Physical Books
  - PDF Downloads
  - Audiobooks

#### 5. **Additional Categories**
- **Funding** - Business investment and partnerships
- **Charities and Donations** - Humanitarian causes
- **Banner** - Banner advertisements
- **Sponsored Ads** - Sponsored, featured, promoted ads
- **Jobs and Vacancies** - Job postings and career opportunities
- **Services** - Fiverr/PeoplePerHour style marketplace
- **Business and Stores** - Business listings and online stores
- **Affiliate Programs** - User affiliate links and program joining

---

## 💰 Upselling System

### Upsell Types & Priority Scores

| Upsell Type | Priority Score | Price (USD) | Duration | Description |
|-------------|---------------|-------------|----------|-------------|
| **Premium** | 1000 | $50.00 | 30 days | Maximum visibility with premium placement |
| **Sponsored** | 800 | $25.00 | 21 days | Top placement with sponsored badge |
| **Featured** | 600 | $15.00 | 14 days | Get a featured badge on your listing |
| **Priority** | 400 | $10.00 | 7 days | Your listing appears first in search results |

### How Upselling Works

1. **Priority Scoring**: Each upsell type has a priority score that affects search ranking
2. **Search Ordering**: Listings are ordered by total priority score (highest first)
3. **Multiple Upsells**: Users can purchase multiple upsells for cumulative priority
4. **Time-based**: Upsells expire after the specified duration

---

## 🛠 API Endpoints

### Categories API

#### Get All Categories
```http
GET /api/v1/categories
```
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "category_id": 1,
      "name": "Buy and Sell",
      "slug": "buy-and-sell",
      "description": "General selling posts - items for sale, swap, or free",
      "parent_id": null,
      "is_active": true,
      "sort_order": 4,
      "posting_form_config": {...},
      "filter_config": {...},
      "children": [...]
    }
  ]
}
```

#### Get Category Tree
```http
GET /api/v1/categories/tree
```

#### Get Category by ID
```http
GET /api/v1/categories/{id}
```

#### Get Category Filters
```http
GET /api/v1/categories/{id}/filters
```

### Upselling API

#### Get Upsell Options
```http
GET /api/v1/upsell/options
Authorization: Bearer {{auth_token}}
```
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "type": "priority",
      "name": "Priority Placement",
      "description": "Your listing appears first in search results",
      "price": 10.00,
      "duration_days": 7,
      "priority_score": 400
    },
    {
      "type": "featured",
      "name": "Featured Listing",
      "description": "Get a featured badge on your listing",
      "price": 15.00,
      "duration_days": 14,
      "priority_score": 600
    },
    {
      "type": "sponsored",
      "name": "Sponsored Listing",
      "description": "Top placement with sponsored badge",
      "price": 25.00,
      "duration_days": 21,
      "priority_score": 800
    },
    {
      "type": "premium",
      "name": "Premium Placement",
      "description": "Maximum visibility with premium placement",
      "price": 50.00,
      "duration_days": 30,
      "priority_score": 1000
    }
  ]
}
```

#### Purchase Upsell
```http
POST /api/v1/upsell/purchase
Authorization: Bearer {{auth_token}}
```
**Request Body:**
```json
{
  "listing_id": 123,
  "upsell_type": "featured",
  "duration_days": 14,
  "payment_method": "stripe"
}
```

#### Get User Upsells
```http
GET /api/v1/upsell/my-upsells
Authorization: Bearer {{auth_token}}
```

#### Get Upsell Statistics
```http
GET /api/v1/upsell/statistics
Authorization: Bearer {{auth_token}}
```

#### Cancel Upsell
```http
DELETE /api/v1/upsell/{upsell_id}
Authorization: Bearer {{auth_token}}
```

### Enhanced Search API

#### Search Listings with Priority
```http
POST /api/v1/search/listings
```
**Request Body:**
```json
{
  "query": "search term",
  "category_id": 1,
  "location": "city",
  "sort_by": "priority", // Options: newest, oldest, price_low, price_high, priority
  "filters": {...}
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "listing_id": 123,
      "title": "Listing Title",
      "priority_score": 1000,
      "upsells": [
        {
          "upsell_type": "premium",
          "expires_at": "2024-02-15T10:30:00Z"
        }
      ]
    }
  ],
  "meta": {
    "total": 150,
    "per_page": 20,
    "current_page": 1
  }
}
```

---

## 🎯 Frontend Integration Guide

### Category Display

```javascript
// Fetch categories with hierarchy
const fetchCategories = async () => {
  const response = await fetch('/api/v1/categories/tree');
  const data = await response.json();
  return data.data;
};

// Render category tree
const renderCategoryTree = (categories) => {
  return categories.map(category => `
    <div class="category-item">
      <h3>${category.name}</h3>
      <p>${category.description}</p>
      ${category.children ? renderSubcategories(category.children) : ''}
    </div>
  `).join('');
};
```

### Upsell Purchase Flow

```javascript
// Get available upsell options
const getUpsellOptions = async () => {
  const response = await fetch('/api/v1/upsell/options', {
    headers: {
      'Authorization': `Bearer ${authToken}`
    }
  });
  return response.json();
};

// Purchase upsell
const purchaseUpsell = async (listingId, upsellType) => {
  const response = await fetch('/api/v1/upsell/purchase', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${authToken}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      listing_id: listingId,
      upsell_type: upsellType,
      duration_days: 14,
      payment_method: 'stripe'
    })
  });
  return response.json();
};
```

### Search with Priority

```javascript
// Search listings with priority ordering
const searchListings = async (query, filters = {}) => {
  const response = await fetch('/api/v1/search/listings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      query: query,
      sort_by: 'priority', // Priority ordering
      ...filters
    })
  });
  return response.json();
};
```

---

## 📱 UI Components

### Category Cards

```html
<div class="category-card">
  <div class="category-icon">
    <img src="/icons/{{category.icon}}" alt="{{category.name}}">
  </div>
  <div class="category-content">
    <h3>{{category.name}}</h3>
    <p>{{category.description}}</p>
    <div class="subcategory-count">
      {{category.children.length}} subcategories
    </div>
  </div>
</div>
```

### Upsell Badges

```html
<div class="upsell-badges">
  <span class="badge premium" v-if="hasPremium">Premium</span>
  <span class="badge sponsored" v-if="hasSponsored">Sponsored</span>
  <span class="badge featured" v-if="hasFeatured">Featured</span>
  <span class="badge priority" v-if="hasPriority">Priority</span>
</div>
```

### Upsell Purchase Modal

```html
<div class="upsell-modal">
  <h2>Promote Your Listing</h2>
  <div class="upsell-options">
    <div class="upsell-option" v-for="option in upsellOptions" :key="option.type">
      <h3>{{option.name}}</h3>
      <p>{{option.description}}</p>
      <div class="price">${{option.price}} / {{option.duration_days}} days</div>
      <button @click="purchaseUpsell(option.type)">Purchase</button>
    </div>
  </div>
</div>
```

---

## 🔧 Admin Panel Features

### Category Management
- ✅ Create/Edit/Delete categories
- ✅ Hierarchical category structure
- ✅ Posting form configuration per category
- ✅ Filter configuration for specialized categories
- ✅ Sort order and activation controls

### Upsell Management
- ✅ View all upsells with status
- ✅ Manual upsell creation
- ✅ Payment status management
- ✅ Bulk actions (mark as paid, expire)
- ✅ Upsell analytics and reporting

---

## 📊 Database Schema

### Categories Table Updates
- `posting_form_config` - JSON field for form configuration
- `filter_config` - JSON field for category-specific filters

### Listing Upsells Table
- `upsell_id` - Primary key
- `listing_id` - Foreign key to listings
- `customer_id` - Foreign key to customers
- `upsell_type` - Type of upsell (priority, featured, sponsored, premium)
- `price` - Cost of upsell
- `duration_days` - How long upsell lasts
- `starts_at` - When upsell becomes active
- `expires_at` - When upsell expires
- `status` - active, expired, cancelled
- `payment_status` - pending, paid, failed, refunded

---

## 🚀 Implementation Status

### ✅ Completed Features
- Category structure updates
- Upselling system backend
- Priority scoring algorithm
- Admin panel UI for categories
- Admin panel UI for upsells
- API endpoints for all features
- Database migrations

### 📋 Next Steps
- Frontend implementation
- Payment gateway integration
- Upsell analytics dashboard
- Email notifications for upsell expiry

---

## 📞 Support

For any questions or issues regarding the categories and upselling system, please refer to:
- API documentation above
- Database schema in migrations
- Admin panel interface
- Code comments in controllers and models

**Last Updated**: January 22, 2026
**Version**: 2.0
