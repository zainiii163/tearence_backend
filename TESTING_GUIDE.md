# 🌱 Database Seeding Complete - Testing Guide

## ✅ **Successfully Populated Database**

### 📊 **Test Data Created**

#### **Admin Users**
- **Super Admin**: rizky@worldwideadverts.info / admin123 (KYC Verified)
- **John Doe**: john.doe@example.com / password123 (KYC Verified)
- **Jane Smith**: jane.smith@example.com / password123 (KYC Pending)
- **Bob Wilson**: bob.wilson@example.com / password123 (KYC Rejected)
- **Alice Brown**: alice.brown@example.com / password123 (No KYC)

#### **Ad Pricing Plans**
- **Banner Plans**:
  - Basic Banner: $29.99/7 days
  - Premium Banner: $99.99/30 days (Featured)
  - Enterprise Banner: $299.99/90 days (Featured)
- **Affiliate Plans**:
  - Starter Affiliate: $19.99/14 days
  - Professional Affiliate: $79.99/60 days (Featured)
  - Premium Affiliate: $199.99/120 days (Featured)
- **Inactive Plans** (for testing admin interface):
  - Legacy Banner: $15.99/3 days
  - Beta Affiliate Plan: $9.99/7 days

#### **Sample Listings**
- **Approved Listings** (3 items):
  - iPhone 13 Pro Max - $899.99 (Regular)
  - MacBook Pro 16" - $2499.99 (Sponsored)
  - Official Platform Announcement (Admin Post)
- **Pending Listings** (2 items):
  - Samsung Galaxy S23 - $799.99 (Regular)
  - iPad Air - $599.99 (Promoted)
- **Rejected Listings** (2 items):
  - "Get Rich Quick" scam - $99.99
  - "Rare Items" prohibited content - $5000.00
- **Harmful Listings** (2 items):
  - iPhone with suspicious pricing - $50.00
  - Bank transfer scam - $100.00
- **Old Listings** (3 items, 30+ days):
  - Expired items for cleanup testing

## 🧪 **Testing Scenarios**

### **KYC Testing**
1. **Login as different users** to test various KYC statuses
2. **Submit KYC documents** using the frontend form
3. **Admin approval/rejection** through Filament panel
4. **Access control** - Users without KYC should be blocked

### **Ad Moderation Testing**
1. **Pending Approval** - New listings should require admin approval
2. **Bulk Operations** - Test approve/reject multiple listings
3. **Harmful Detection** - Run automated detection command
4. **Post Types** - Test Sponsored/Promoted/Admin posts
5. **Reposting** - Test date updates and re-approval

### **Payment Testing**
1. **Banner Purchase** - Test banner ad payment flow
2. **Affiliate Purchase** - Test affiliate ad payment flow
3. **Revenue Tracking** - Verify payment records created
4. **Pricing Plans** - Test plan creation and management

## 🔧 **API Testing Commands**

### **Manual Testing**
```bash
# Test harmful content detection
php artisan ads:moderate-harmful

# Test old ad cleanup
php artisan ads:delete-old 21

# Test KYC statistics
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/kyc/statistics

# Test ad approval statistics
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/ads/moderation-stats
```

### **Frontend Testing**
1. **KYC Submission**: Visit `/kyc-submission`
2. **User Dashboard**: Visit `/dashboard`
3. **Admin Panel**: Visit `/admin`
4. **API Collection**: Import into Postman/Insomnia

## 📱 **Expected Behaviors**

### **Security Features**
- ✅ Users without KYC cannot post ads
- ✅ All ads require admin approval
- ✅ Harmful content automatically flagged
- ✅ Old ads automatically cleaned up
- ✅ Admin posts have special indicators

### **Admin Controls**
- ✅ Bulk approval/rejection capabilities
- ✅ Advanced filtering and search
- ✅ Real-time statistics dashboard
- ✅ Document viewing for KYC
- ✅ Special post type management

### **User Experience**
- ✅ Clear status indicators
- ✅ Responsive design
- ✅ Real-time updates
- ✅ Error handling and feedback
- ✅ Progress indicators

## 🚀 **Ready for Full Testing**

The system is now fully populated with comprehensive test data covering all scenarios:
- Different KYC statuses
- Various approval states
- Harmful content examples
- Payment processing scenarios
- Admin role testing

All features can now be thoroughly tested using the provided test accounts and data!
