# Staff Management User Validation Implementation

## Overview

This implementation ensures that business and store page admins can only add **registered users** to their teams. If a person is not registered, the admin is prompted to ask them to sign up first.

## New API Endpoints

### 1. Search Users
**POST** `/api/v1/staff/search-users`

Search for registered users by email or phone number.

**Request:**
```json
{
    "search": "john@example.com" // or phone number, min 3 characters
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "customer_id": 123,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "phone_number": "+1234567890"
        }
    ],
    "message": "Users found successfully"
}
```

### 2. Check and Invite User
**POST** `/api/v1/staff/check-and-invite`

Check if a user exists by email/phone and provide appropriate response.

**Request:**
```json
{
    "email": "jane@example.com", // optional if phone provided
    "phone": "+1234567890", // optional if email provided
    "entity_type": "business", // or "store"
    "entity_id": 456,
    "role": "admin" // or "editor", "viewer"
}
```

**Response - User Exists (200 OK):**
```json
{
    "success": true,
    "data": {
        "user_exists": true,
        "user": {
            "customer_id": 789,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "phone_number": "+1234567890"
        },
        "message": "User is already registered. You can add them as staff now."
    },
    "message": "User found"
}
```

**Response - User Not Found (200 OK):**
```json
{
    "success": true,
    "data": {
        "user_exists": false,
        "contact_info": "jane@example.com",
        "message": "User is not registered. Please ask them to sign up first using this email/phone number.",
        "signup_url": "http://your-domain.com/register"
    },
    "message": "User not found - invitation needed"
}
```

### 3. Add Staff Member (Enhanced)
**POST** `/api/v1/staff/add-staff-member`

Add a staff member with flexible user identification and validation.

**Request Options:**

**Option 1 - By Customer ID:**
```json
{
    "customer_id": 789,
    "entity_type": "business",
    "entity_id": 456,
    "role": "admin",
    "can_post_ads": true,
    "can_edit_ads": true
}
```

**Option 2 - By Email:**
```json
{
    "email": "jane@example.com",
    "entity_type": "business", 
    "entity_id": 456,
    "role": "admin"
}
```

**Option 3 - By Phone:**
```json
{
    "phone": "+1234567890",
    "entity_type": "business",
    "entity_id": 456, 
    "role": "admin"
}
```

**Response - Success (201 Created):**
```json
{
    "success": true,
    "data": {
        "staff_id": 101,
        "customer_id": 456,
        "staff_customer_id": 789,
        "entity_type": "business",
        "entity_id": 456,
        "role": "admin",
        "staffMember": {
            "customer_id": 789,
            "name": "Jane Smith",
            "email": "jane@example.com"
        }
    },
    "message": "Staff member added successfully"
}
```

**Response - User Not Found (404 Not Found):**
```json
{
    "success": false,
    "error": "User not found. Please ask them to register first."
}
```

## Implementation Details

### Customer Model Enhancements

Two new static methods added to `Customer` model:

1. **`findByEmailOrPhone($email, $phone)`** - Find exact match by email or phone
2. **`searchByEmailOrPhone($search)`** - Search with partial matching (min 3 chars)

### StaffManagementController Enhancements

1. **`searchUsers()`** - Search registered users
2. **`checkAndInviteUser()`** - Validate user existence and provide guidance
3. **`addStaffMember()`** - Enhanced staff addition with flexible user identification

### Security Features

- **Ownership Verification**: Only business/store owners can add staff
- **Self-Prevention**: Users cannot add themselves as staff
- **Duplicate Prevention**: Same user cannot be added twice to same entity
- **Authentication Required**: All endpoints require valid JWT token
- **Entity Validation**: Verifies business/store exists and belongs to user

## Usage Flow

### Frontend Implementation Suggestion

1. **User Input**: Admin enters email/phone number
2. **Search/Check**: Call `/check-and-invite` endpoint
3. **Handle Response**:
   - If `user_exists: true` → Show user info and confirm to add
   - If `user_exists: false` → Show message with signup link
4. **Add Staff**: If confirmed, call `/add-staff-member`

### Example Frontend Flow

```javascript
async function addStaffMember(email, phone, entityType, entityId, role) {
    // Step 1: Check if user exists
    const checkResponse = await fetch('/api/v1/staff/check-and-invite', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, phone, entityType, entityId, role })
    });
    
    const result = await checkResponse.json();
    
    if (result.data.user_exists) {
        // Step 2: Confirm and add the user
        if (confirm(`Add ${result.data.user.name} as staff?`)) {
            const addResponse = await fetch('/api/v1/staff/add-staff-member', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, phone, entityType, entityId, role })
            });
            
            return await addResponse.json();
        }
    } else {
        // Step 3: Show invitation message
        alert(`User not found. Please ask them to sign up at: ${result.data.signup_url}`);
    }
}
```

## Benefits

1. **Data Integrity**: Only registered users can be added as staff
2. **User Experience**: Clear guidance when user is not registered
3. **Security**: Proper validation and authorization checks
4. **Flexibility**: Multiple ways to identify users (ID, email, phone)
5. **Prevention**: Avoids duplicate staff assignments

## Testing

Test the following scenarios:

1. ✅ Add existing user by email
2. ✅ Add existing user by phone  
3. ✅ Add existing user by customer_id
4. ✅ Try to add non-existent user (should show invitation message)
5. ✅ Try to add yourself (should be blocked)
6. ✅ Try to duplicate staff member (should be blocked)
7. ✅ Try without authentication (should be blocked)
8. ✅ Try with invalid entity (should be blocked)

This implementation ensures that business and store admins can only add registered users while providing a smooth user experience for inviting new users to join the platform.
