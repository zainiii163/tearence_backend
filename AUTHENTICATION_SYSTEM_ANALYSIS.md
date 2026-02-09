# Authentication System Analysis: Admin vs Customer Separation

## Overview
The WWA API system implements a **dual authentication system** with complete separation between Admin users and Customer users.

## Database Structure

### 1. Admin Users (`user` table)
- **Model**: `App\Models\User`
- **Primary Key**: `user_id`
- **Table**: `user`
- **Password Field**: `password`
- **Purpose**: Administrative access, system management

### 2. Customer Users (`customer` table)
- **Model**: `App\Models\Customer`
- **Primary Key**: `customer_id`
- **Table**: `customer`
- **Password Field**: `password_hash`
- **Purpose**: End-user accounts, posting listings, general platform usage

## Authentication Guards

### Web Guards (Session-based)
1. **`web` guard**
   - Driver: `session`
   - Provider: `customers`
   - Model: `App\Models\Customer`
   - Used for: Customer web login

2. **`admin-web` guard**
   - Driver: `session`
   - Provider: `users`
   - Model: `App\Models\User`
   - Used for: Admin web login

### API Guards (Token-based)
1. **`api` guard**
   - Driver: `jwt`
   - Provider: `customers`
   - Model: `App\Models\Customer`
   - Used for: Customer API authentication

2. **`admin` guard**
   - Driver: `jwt`
   - Provider: `users`
   - Model: `App\Models\User`
   - Used for: Admin API authentication

## Login Flow Logic

### AuthenticatedSessionController::store()
The login controller follows this logic:

1. **Check Admin First**
   ```php
   $adminUser = User::where('email', $credentials['email'])->first();
   if ($adminUser && Hash::check($credentials['password'], $adminUser->password)) {
       if (Auth::guard('admin-web')->attempt($credentials)) {
           // Redirect to /admin
       }
   }
   ```

2. **Check Customer Second**
   ```php
   $customer = Customer::where('email', $credentials['email'])->first();
   if ($customer && Hash::check($credentials['password'], $customer->password_hash)) {
       if (Auth::guard('web')->attempt($credentials)) {
           // Redirect to /dashboard
       }
   }
   ```

3. **Fail if Neither Found**
   - Return error: "The provided credentials do not match our records."

## Key Differences

### Admin Users (User Model)
- **Permissions System**: Granular permissions (`can_manage_users`, `can_manage_categories`, etc.)
- **KYC Integration**: Full KYC status tracking
- **Super Admin Flag**: `is_super_admin` boolean
- **Posting Limits**: `posts_limit`, `posts_count` tracking
- **Panel Access**: Filament admin panel integration

### Customer Users (Customer Model)
- **JWT Tokens**: Implements `JWTSubject` for API authentication
- **API Tokens**: Uses Laravel Sanctum
- **Business Integration**: Related to businesses, stores, campaigns
- **Location & Currency**: Built-in location and currency relationships
- **File Uploads**: Avatar handling with `FileUploadHelper`

## Password Field Differences

### Admin Users
- Field: `password`
- Standard Laravel bcrypt hashing

### Customer Users
- Field: `password_hash`
- Custom password accessor via `getAuthPassword()` method
- Same bcrypt hashing but different field name

## Session Management

### Logout Logic
```php
public function destroy(Request $request)
{
    if (Auth::guard('admin-web')->check()) {
        Auth::guard('admin-web')->logout();
    } else {
        Auth::guard('web')->logout();
    }
    // Session cleanup...
}
```

## Security Implications

### Complete Separation Benefits
1. **Isolation**: Admin and customer data are completely separate
2. **Permission Control**: Different permission systems for each user type
3. **Access Control**: Different routes and dashboards
4. **Token Management**: Separate JWT tokens for API access

### Potential Issues
1. **Email Collision**: Same email cannot exist in both tables
2. **Password Field Names**: Different field names require custom logic
3. **Multiple Guards**: Need to check both guards for authentication status

## Current Implementation Status

✅ **Properly Separated**
- Different database tables
- Different authentication guards
- Different login logic
- Different password fields

✅ **Working Correctly**
- Admin login uses `admin-web` guard
- Customer login uses `web` guard
- Proper redirection based on user type
- Session management handles both types

## Recommendations

### For Development
1. **Always check the right guard** when checking authentication
2. **Use the correct model** for user type operations
3. **Remember password field differences** when validating credentials
4. **Test both user types** when making authentication changes

### Example Usage
```php
// Check if admin is logged in
if (Auth::guard('admin-web')->check()) {
    $admin = Auth::guard('admin-web')->user();
    // Admin logic
}

// Check if customer is logged in
if (Auth::guard('web')->check()) {
    $customer = Auth::guard('web')->user();
    // Customer logic
}
```

## Test Users

### Admin User
- **Email**: rizky@worldwideadverts.info
- **Password**: admin123
- **Table**: user
- **Guard**: admin-web

### Customer Users
- **Email**: john.doe@example.com
- **Password**: password123
- **Table**: customer
- **Guard**: web

This separation ensures that admin and customer functionalities remain completely isolated and secure.
