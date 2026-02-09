# User Referral System Documentation

## 🎯 Overview

The User Referral System allows users to invite others to join the platform and earn discounts on adverts. Both the referrer and the referred user receive benefits when the referral is successful.

## 🎁 Benefits Structure

### For New Users (Referred Users)
- **20% discount** on their first advert
- Applied automatically when they create their first listing
- One-time welcome discount

### For Referrers (Existing Users)
- **10% discount** on their next advert for each successful referral
- Applied when the referred user posts their first listing
- Can accumulate multiple discounts from different referrals

## 📊 Database Schema

### Referrals Table
- `referral_id` - Primary key
- `referrer_id` - Customer who created the referral
- `referral_code` - Unique 8-character code
- `referral_link` - Full registration link
- `message` - Personal message from referrer
- `is_active` - Whether referral is active
- `max_uses` - Maximum times code can be used (default: 50)
- `current_uses` - Current usage count
- `expires_at` - Expiration date (default: 6 months)

### User Referrals Table
- `user_referral_id` - Primary key
- `referral_id` - Link to referral
- `referred_user_id` - User who was referred
- `referrer_user_id` - User who referred
- `status` - pending, completed, expired
- `registered_at` - When referred user registered
- `completed_at` - When referral was completed
- `referrer_discount_amount` - Discount amount for referrer (10.00)
- `referred_discount_amount` - Discount amount for referred user (20.00)
- `referrer_discount_type` - Discount type (percentage/fixed)
- `referred_discount_type` - Discount type (percentage/fixed)
- `referrer_discount_used` - Whether referrer discount was used
- `referred_discount_used` - Whether referred user discount was used

## 🔧 API Endpoints

### Public Endpoints (No Authentication Required)

#### Validate Referral Code
```
POST /api/v1/referral/validate
{
  "code": "ABC12345"
}
```

#### Get Referral Info (for Registration Page)
```
GET /api/v1/referral/info?code=ABC12345
```

### Protected Endpoints (Authentication Required)

#### Get My Referral
```
GET /api/v1/referral/my
```
Returns user's referral code, link, and statistics.

#### Create Referral
```
POST /api/v1/referral/create
{
  "message": "Join me on this amazing platform!",
  "max_uses": 50,
  "expires_at": "2026-07-27"
}
```

#### Update Referral
```
PUT /api/v1/referral/{referral_id}
{
  "message": "Updated message",
  "is_active": true
}
```

#### Get Referral History
```
GET /api/v1/referral/history
```
Returns all referral activities and available discounts.

#### Share Referral
```
GET /api/v1/referral/{referral_id}/share
```
Returns shareable links for different platforms.

## 🔄 How It Works

### 1. User Registration with Referral
```json
POST /api/v1/auth/register
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "referral_code": "ABC12345"
}
```

### 2. First Listing Creation (Completes Referral)
When the referred user creates their first listing:
- Referral status changes from "pending" to "completed"
- Referrer earns a 10% discount
- Referred user gets 20% discount on this listing

### 3. Discount Application
Discounts are automatically applied when creating listings:
```json
POST /api/v1/listing
{
  "title": "My Product",
  "description": "Product description",
  "price": 100.00,
  // ... other fields
}
```

Response with discount applied:
```json
{
  "success": true,
  "data": {
    "listing": {
      "price": 80.00,  // 20% discount applied
      // ... other listing data
    },
    "discount_applied": {
      "original_price": 100.00,
      "discount_amount": 20.00,
      "final_price": 80.00,
      "discount_applied": true,
      "discount_source": "welcome_referral"
    }
  }
}
```

## 🎯 Features

### Automatic Code Generation
- 8-character unique codes (e.g., "ABC12345")
- Case-insensitive validation
- Automatic link generation

### Smart Validation
- Prevents self-referral
- Checks for duplicate referrals
- Validates expiration and usage limits

### Flexible Discounts
- Percentage-based discounts (default)
- Fixed amount discounts supported
- One-time use per discount

### Comprehensive Tracking
- Registration tracking
- Completion tracking (first listing)
- Discount usage tracking
- Statistics and analytics

### Social Sharing
- Pre-built share links for:
  - Email
  - WhatsApp
  - Twitter
  - Facebook
- Customizable referral messages

## 📈 Statistics & Analytics

### Referrer Statistics
```json
{
  "stats": {
    "total_uses": 15,
    "remaining_uses": 35,
    "completed_referrals": 8,
    "pending_referrals": 7,
    "conversion_rate": 53.33
  }
}
```

### Available Discounts
```json
{
  "available_discounts": [
    {
      "type": "welcome",
      "info": {
        "amount": 20.00,
        "type": "percentage",
        "description": "20% discount"
      },
      "description": "Welcome discount for joining through a referral"
    },
    {
      "type": "referrer_reward",
      "info": {
        "amount": 10.00,
        "type": "percentage", 
        "description": "10% discount"
      },
      "description": "Reward for successful referral",
      "referred_user": "Jane Smith"
    }
  ]
}
```

## 🔒 Security Features

- **Self-referral prevention**: Users cannot refer themselves
- **Duplicate prevention**: Users can only be referred once
- **Code expiration**: Automatic expiration after 6 months
- **Usage limits**: Maximum 50 uses per referral code
- **Fraud detection**: Tracks IP addresses and registration patterns

## 🚀 Implementation Details

### Model Relationships
```php
// Customer Model
public function referral()
{
    return $this->hasOne(Referral::class, 'referrer_id', 'customer_id');
}

public function sentReferrals()
{
    return $this->hasMany(UserReferral::class, 'referrer_user_id', 'customer_id');
}

public function receivedReferral()
{
    return $this->hasOne(UserReferral::class, 'referred_user_id', 'customer_id');
}
```

### Service Integration
- `ReferralService::processRegistrationReferral()` - Handle registration referrals
- `ReferralService::completeReferral()` - Complete referral on first listing
- `ReferralService::applyReferralDiscount()` - Apply discounts to listings

### Automatic Processing
- Referral codes generated automatically on first access
- Discounts applied automatically during listing creation
- Referral completion triggered by first listing post

## 📱 Frontend Integration Tips

### Registration Form
```html
<input type="text" name="referral_code" placeholder="Referral Code (optional)">
```

### Referral Sharing Component
```javascript
// Get referral data
GET /api/v1/referral/my

// Display sharing options
- Copy link button
- Social sharing buttons
- QR code generation
```

### Discount Display
```javascript
// Check available discounts
GET /api/v1/referral/history

// Show discount banners
- "You have a 20% welcome discount!"
- "You earned a 10% discount from referring Jane!"
```

## 🎯 Success Metrics

Track these metrics to measure referral program success:
- **Referral conversion rate**: % of referred users who complete registration
- **Activation rate**: % of referred users who post first listing
- **Discount usage rate**: % of discounts that are actually used
- **Referral ROI**: Revenue generated vs. discounts given
- **Viral coefficient**: Average number of referrals per user

## 🔧 Configuration

### Environment Variables (Optional)
```env
REFERRAL_DEFAULT_MAX_USES=50
REFERRAL_DEFAULT_EXPIRY_MONTHS=6
REFERRED_DISCOUNT_PERCENTAGE=20
REFERRER_DISCOUNT_PERCENTAGE=10
```

### Customization
- Discount amounts can be customized per campaign
- Expiration periods can be adjusted
- Maximum usage limits configurable
- Custom messaging supported

---

## ✅ Ready for Production

The referral system is fully implemented and ready for production use with:
- ✅ Complete database schema
- ✅ Comprehensive API endpoints
- ✅ Automatic discount processing
- ✅ Security measures in place
- ✅ Analytics and tracking
- ✅ Social sharing capabilities
- ✅ Flexible configuration options

Users can now invite friends and earn discounts while growing the platform organically!
