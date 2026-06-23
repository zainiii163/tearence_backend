# Admin Access Control Documentation

## Overview

The admin panel uses a permission-based access control system to restrict what different admin users can access and modify. This system allows the super admin to create limited admin accounts with specific permissions.

## Permission Flags

The User model has the following permission flags:

| Flag | Description | Purpose |
|------|-------------|---------|
| `is_super_admin` | Super Admin | Has full system access, overrides all other permissions |
| `can_manage_users` | Can Manage Users | Can create, edit, and delete user accounts |
| `can_manage_categories` | Can Manage Categories | Can create, edit, and delete categories |
| `can_manage_listings` | Can Manage Listings | Can create, edit, and delete listings/content |
| `can_manage_dashboard` | Can Manage Dashboard | Can access dashboard and financial data |
| `can_view_analytics` | Can View Analytics | Can view analytics and reports |

## Admin Panel Access

Users can access the admin panel if they have ANY of the following permissions:
- `is_super_admin = true`
- `can_manage_dashboard = true`
- `can_manage_users = true`
- `can_manage_listings = true`
- `can_manage_categories = true`

This is controlled by the `canAccessPanel()` method in `app/Models/User.php`.

## Gate Policies

Gate policies are defined in `app/Providers/Filament/AdminPanelProvider.php`:

- `view-user-management` - Requires `is_super_admin` or `can_manage_users`
- `view-analytics` - Requires `is_super_admin` or `can_view_analytics`
- `view-dashboard` - Requires `is_super_admin` or `can_manage_dashboard`
- `view-financial` - Requires `is_super_admin` or `can_manage_dashboard`

## Resource Access Control

Individual Filament resources can restrict access using static methods:

```php
public static function canViewAny(): bool
{
    return auth()->user()->is_super_admin || auth()->user()->can_manage_users;
}
```

Currently protected resources:
- **UserResource** - Requires `is_super_admin` or `can_manage_users`

## Creating Admin Accounts

### Method 1: Using Filament Admin Panel

1. Login as super admin
2. Navigate to Admin Management → Users
3. Click "Create User"
4. Fill in user details
5. In the "Permissions" section, set the appropriate permission flags
6. Save

### Method 2: Using Seeder

Use the `LimitedAdminSeeder` to create admin accounts with predefined permissions:

```bash
php artisan db:seed --class=LimitedAdminSeeder
```

### Method 3: Using Tinker

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'user_uid' => 'UNIQUE_UID',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password123'),
    'group_id' => 1,
    'is_super_admin' => false,
    'can_manage_users' => false,
    'can_manage_categories' => true,
    'can_manage_listings' => true,
    'can_manage_dashboard' => false,
    'can_view_analytics' => false,
    'email_verified' => true,
    'email_verified_at' => now(),
]);
```

## Existing Admin Accounts

### Shihab (Content Admin)
- **Email:** `shihab@worldwideadverts.info`
- **Password:** `Admin@123` (should be changed on first login)
- **Permissions:**
  - ✅ Can manage categories
  - ✅ Can manage listings
  - ❌ Cannot manage users
  - ❌ Cannot access dashboard (financial data)
  - ❌ Cannot view analytics

### Vikas (Content Admin)
- **Email:** `vikas@worldwideadverts.info`
- **Password:** `Admin@123` (should be changed on first login)
- **Permissions:**
  - ✅ Can manage categories
  - ✅ Can manage listings
  - ❌ Cannot manage users
  - ❌ Cannot access dashboard (financial data)
  - ❌ Cannot view analytics

### Super Admin (Rizky)
- **Email:** `rizky@worldwideadverts.info`
- **Permissions:** Full access (is_super_admin = true)

## Recommended Role Templates

### Content Editor
Can post and edit content but cannot access financial data or manage users:
```php
'is_super_admin' => false,
'can_manage_users' => false,
'can_manage_categories' => true,
'can_manage_listings' => true,
'can_manage_dashboard' => false,
'can_view_analytics' => false,
```

### Content Manager
Can manage content and view analytics but cannot manage users:
```php
'is_super_admin' => false,
'can_manage_users' => false,
'can_manage_categories' => true,
'can_manage_listings' => true,
'can_manage_dashboard' => false,
'can_view_analytics' => true,
```

### User Manager
Can manage users and content but cannot access financial data:
```php
'is_super_admin' => false,
'can_manage_users' => true,
'can_manage_categories' => true,
'can_manage_listings' => true,
'can_manage_dashboard' => false,
'can_view_analytics' => false,
```

### Full Admin (Non-Super)
Can do everything except system-level changes:
```php
'is_super_admin' => false,
'can_manage_users' => true,
'can_manage_categories' => true,
'can_manage_listings' => true,
'can_manage_dashboard' => true,
'can_view_analytics' => true,
```

## Security Best Practices

1. **Password Changes:** All admin accounts should change their passwords on first login
2. **Principle of Least Privilege:** Grant only the minimum permissions needed for the role
3. **Regular Audits:** Periodically review admin permissions and access logs
4. **Two-Factor Authentication:** Consider implementing 2FA for admin accounts
5. **Session Management:** Set appropriate session timeouts for admin accounts

## Files Modified

1. `app/Models/User.php` - Updated `canAccessPanel()` method
2. `app/Filament/Resources/UserResource.php` - Added gate checks
3. `app/Providers/Filament/AdminPanelProvider.php` - Added gate policies
4. `database/seeders/LimitedAdminSeeder.php` - Created seeder for limited admin accounts

## Future Enhancements

1. Add gate checks to all financial/analytics resources
2. Add gate checks to Settings resources
3. Implement role-based groups for easier permission management
4. Add audit logging for admin actions
5. Implement IP whitelisting for admin access
6. Add two-factor authentication support

## Troubleshooting

### User cannot access admin panel
- Check if user has at least one of: `is_super_admin`, `can_manage_dashboard`, `can_manage_users`, `can_manage_listings`, or `can_manage_categories`
- Verify user is authenticated
- Check browser console for errors

### User can access restricted resources
- Ensure gate checks are added to the specific resource
- Clear application cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`

### Permission changes not taking effect
- Clear all caches
- Log out and log back in
- Check if gates are properly defined in AdminPanelProvider

## Support

For issues or questions about admin access control, contact the development team or refer to Laravel Filament documentation: https://filamentphp.com/docs
