# WWA API - Monetization & Advertising Platform Documentation

## Overview

This document outlines the comprehensive monetization system implemented for the WWA (Worldwide Adverts) API platform. The system enables paid posting for banner ads, affiliate ads, and classified ads, with full revenue tracking and administrative management.

## Features Implemented

### ✅ **Banner Ads System**
- **Paid Posting**: Users must pay to post banner advertisements
- **Pricing Plans**: Multiple pricing tiers with different durations
- **Payment Integration**: Support for PayPal, Stripe, and bank transfers
- **Expiry Management**: Automatic expiration based on plan duration
- **Admin Control**: Full CRUD operations via Filament admin panel

### ✅ **Affiliate Ads System**
- **Paid Posting**: Users must pay to post affiliate advertisements
- **Pricing Plans**: Multiple pricing tiers with different durations
- **Payment Integration**: Support for PayPal, Stripe, and bank transfers
- **Expiry Management**: Automatic expiration based on plan duration
- **Admin Control**: Full CRUD operations via Filament admin panel

### ✅ **Classified Ads System**
- **Category-Based**: Organized by categories (services, events, business)
- **Filter Configuration**: Advanced filtering options per category
- **Hierarchical Structure**: Parent-child category relationships

### ✅ **Revenue Tracking**
- **Complete Payment History**: Track all ad payments
- **Multiple Revenue Types**: Banner ads, affiliate ads, job upsells, candidate upsells
- **Transaction Management**: Link payments to specific ads and users
- **Revenue Analytics**: Comprehensive reporting capabilities

### ✅ **User Dashboard Enhancement**
- **Ad Management**: View all user's banner and affiliate ads
- **Payment Statistics**: Track spending and ad performance
- **Expiry Notifications**: See ads expiring soon
- **Revenue History**: Complete payment transaction history

### ✅ **Super Admin Capabilities**
- **Complete Content Control**: Edit all ads, user profiles, businesses, stores
- **Payment Status Management**: Monitor and update payment statuses
- **Revenue Analytics**: View platform revenue and user spending
- **Pricing Plan Management**: Create and manage ad pricing tiers

## Database Schema Changes

### New Tables

#### `ad_pricing_plans`
```sql
- id (Primary)
- name (string, 100)
- ad_type (enum: banner, affiliate, classified)
- price (decimal, 10,2)
- duration_days (integer)
- description (text, nullable)
- features (json, nullable)
- is_active (boolean)
- is_featured (boolean)
- sort_order (integer)
- timestamps
```

### Updated Tables

#### `banner` table - Added fields:
```sql
- price (decimal, 10,2) - Cost of the banner ad
- payment_status (enum: pending, paid, failed) - Payment status
- payment_transaction_id (string, nullable) - Transaction reference
- paid_at (timestamp, nullable) - When payment was completed
- expires_at (timestamp, nullable) - When ad expires
- is_active (boolean) - Whether ad is currently active
```

#### `affiliate_links` table - Added fields:
```sql
- price (decimal, 10,2) - Cost of the affiliate ad
- payment_status (enum: pending, paid, failed) - Payment status
- payment_transaction_id (string, nullable) - Transaction reference
- paid_at (timestamp, nullable) - When payment was completed
- expires_at (timestamp, nullable) - When ad expires
- is_active (boolean) - Whether ad is currently active
```

#### `revenue_tracking` table - Added fields:
```sql
- ad_type (string, 20, nullable) - Type of ad (banner, affiliate)
- banner_id (bigInteger, nullable) - Reference to banner
- affiliate_id (bigInteger, nullable) - Reference to affiliate
- description (text, nullable) - Payment description
- Updated revenue_type enum to include 'banner_ad', 'affiliate_ad'
```

## API Endpoints

### Banner Ads
```
GET    /v1/banner/pricing-plans     - Get available pricing plans
POST   /v1/banner/payment          - Process banner payment
GET    /v1/banner                  - List all banners
POST   /v1/banner                  - Create new banner (requires payment)
GET    /v1/banner/{id}              - Get banner details
PUT    /v1/banner/{id}              - Update banner
DELETE /v1/banner/{id}              - Delete banner
GET    /v1/banner/my-banner         - Get user's banners
```

### Affiliate Ads
```
GET    /v1/affiliate/pricing-plans  - Get available pricing plans
POST   /v1/affiliate/payment       - Process affiliate payment
GET    /v1/affiliate               - List all affiliates
POST   /v1/affiliate               - Create new affiliate (requires payment)
GET    /v1/affiliate/{id}           - Get affiliate details
PUT    /v1/affiliate/{id}           - Update affiliate
DELETE /v1/affiliate/{id}           - Delete affiliate
GET    /v1/affiliate/my-affiliate   - Get user's affiliates
```

### Classified Ads
```
GET    /v1/classified              - List categories
GET    /v1/classified/{slug}       - Get listings by category
```

### User Dashboard (Enhanced)
```
GET    /v1/dashboard               - Get user dashboard with ad statistics
```

## Payment Flow

### 1. **Pricing Plan Selection**
- User calls `/v1/banner/pricing-plans` or `/v1/affiliate/pricing-plans`
- System returns available pricing plans with costs and durations

### 2. **Payment Processing**
- User calls `/v1/banner/payment` or `/v1/affiliate/payment`
- System creates revenue tracking record
- Payment is processed via selected method (PayPal/Stripe/Bank)

### 3. **Ad Creation**
- User calls `/v1/banner` or `/v1/affiliate` with payment transaction ID
- System validates payment and creates ad with expiry date
- Ad becomes active immediately

### 4. **Revenue Tracking**
- All payments are tracked in `revenue_tracking` table
- Links payments to specific ads and users
- Provides complete audit trail

## Admin Panel Features

### **Banner Management**
- View all banner ads with payment status
- Filter by payment status, active status
- Edit banner details, payment status, expiry dates
- Delete inactive banners

### **Affiliate Management**
- View all affiliate ads with payment status
- Filter by payment status, active status
- Edit affiliate details, payment status, expiry dates
- Delete inactive affiliates

### **Pricing Plan Management**
- Create and manage pricing tiers
- Set different prices for banner/affiliate/classified ads
- Configure durations and features
- Mark plans as featured

### **Revenue Analytics**
- View all payment transactions
- Filter by ad type, payment status, date range
- Track platform revenue and user spending
- Export payment reports

## User Dashboard Features

### **Ad Statistics**
```json
{
  "banner_ads": {
    "my_banners": [...],
    "stats": {
      "total_banners": 5,
      "active_banners": 3,
      "expired_banners": 1,
      "pending_payment": 1,
      "total_spent_banners": 150.00
    },
    "expiring_soon": [...]
  },
  "affiliate_ads": {
    "my_affiliates": [...],
    "stats": {
      "total_affiliates": 3,
      "active_affiliates": 2,
      "expired_affiliates": 0,
      "pending_payment": 1,
      "total_spent_affiliates": 75.00
    },
    "expiring_soon": [...]
  },
  "ad_revenue": [...]
}
```

### **Expiry Management**
- Automatic detection of ads expiring in 7 days
- Visual indicators for expiring ads
- Renewal prompts for expired ads

## Security Features

### **Payment Validation**
- All ad creation requires valid payment transaction ID
- System verifies payment before activating ads
- Prevents unauthorized free posting

### **Access Control**
- Users can only view/manage their own ads
- Admins have full access to all content
- Role-based permissions in admin panel

### **Data Integrity**
- Foreign key constraints ensure data consistency
- Transaction rollback on payment failures
- Audit trail for all payment operations

## Migration Instructions

### 1. **Run Database Migrations**
```bash
php artisan migrate
```

### 2. **Seed Pricing Plans** (Optional)
```bash
php artisan db:seed --class=AdPricingPlanSeeder
```

### 3. **Clear Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Configuration

### **Payment Methods**
Configure payment gateways in `.env`:
```env
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
STRIPE_PUBLIC_KEY=your_stripe_public_key
STRIPE_SECRET_KEY=your_stripe_secret_key
```

### **Ad Settings**
Configure ad settings in `config/ads.php`:
```php
return [
    'default_banner_price' => 10.00,
    'default_affiliate_price' => 5.00,
    'default_duration_days' => 30,
    'max_file_size' => 512, // KB
    'allowed_image_types' => ['jpeg', 'png', 'jpg', 'gif'],
];
```

## Testing

### **Payment Flow Testing**
```bash
# Test pricing plans endpoint
curl -X GET "http://localhost:8000/api/v1/banner/pricing-plans"

# Test payment processing
curl -X POST "http://localhost:8000/api/v1/banner/payment" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "pricing_plan_id": 1,
    "payment_method": "paypal",
    "transaction_id": "TEST_123456"
  }'

# Test banner creation
curl -X POST "http://localhost:8000/api/v1/banner" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test Banner",
    "url_link": "https://example.com",
    "pricing_plan_id": 1,
    "payment_transaction_id": "TEST_123456"
  }'
```

## Future Enhancements

### **Planned Features**
1. **Automated Renewals**: Auto-renew ads before expiry
2. **Promotional Codes**: Discount codes for ad posting
3. **Bulk Discounts**: Volume pricing for multiple ads
4. **Advanced Analytics**: Click-through rates, conversion tracking
5. **Ad Approval Workflow**: Admin approval before ad activation
6. **Geographic Targeting**: Location-based ad display
7. **A/B Testing**: Test different ad creatives
8. **API Rate Limiting**: Prevent abuse of free endpoints

### **Performance Optimizations**
1. **Caching**: Cache pricing plans and popular ads
2. **CDN Integration**: Serve ad images via CDN
3. **Database Indexing**: Optimize queries for large datasets
4. **Queue System**: Process payments asynchronously

## Support & Maintenance

### **Monitoring**
- Monitor payment success rates
- Track ad expiration and renewal rates
- Monitor API response times
- Track user engagement with ads

### **Backup Strategy**
- Daily database backups
- Image file backups to cloud storage
- Revenue data export for accounting

### **Compliance**
- GDPR compliance for user data
- Payment card industry (PCI) compliance
- Tax reporting for revenue tracking

## Conclusion

The WWA API platform now includes a comprehensive monetization system that enables:
- Paid posting for banner and affiliate ads
- Flexible pricing plans with different durations
- Complete revenue tracking and analytics
- Enhanced user dashboard with ad management
- Full administrative control via Filament admin panel

This system provides a solid foundation for generating revenue while maintaining excellent user experience and administrative control.

---

**Last Updated**: January 16, 2026
**Version**: 1.0.0
**Author**: WWA Development Team
