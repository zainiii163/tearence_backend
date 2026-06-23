# Frontend API Fixes - Complete Solution

## 🚨 Critical Issues Identified

### 1. **Duplicate /v1/v1/ Path Issue** (MAIN PROBLEM)
**Error**: `:8000/api/v1/v1/dashboard/user` (404 Not Found)
**Root Cause**: Frontend is adding `/v1/` to an already prefixed `/api/v1/` base URL

### 2. **Missing Books API Functions**
**Error**: `getTrendingGenres is not a function`
**Root Cause**: Function not implemented in booksAPI service

### 3. **Destructuring Errors in Redux Slices**
**Error**: `Cannot destructure property 'is_parent' of 'undefined'`
**Root Cause**: API responses are undefined/null when endpoints fail

---

## 🔧 IMMEDIATE FIXES REQUIRED

### Fix 1: API Base URL Configuration

**Find your frontend API configuration file** (likely one of these):
- `src/api/api.js`
- `src/services/api.js` 
- `src/config/api.js`
- `src/utils/axios.js`

**Current WRONG configuration:**
```javascript
// This causes the /v1/v1/ duplication
const API_BASE_URL = 'http://localhost:8000/api/v1'
// Then frontend calls: api.get('/v1/dashboard/user') 
// Results in: /api/v1/v1/dashboard/user ❌
```

**CORRECT configuration:**
```javascript
// Remove /v1/ from individual endpoint calls
const API_BASE_URL = 'http://localhost:8000/api/v1'

// Then call endpoints WITHOUT /v1/ prefix:
api.get('/dashboard/user')        // ✅ /api/v1/dashboard/user
api.get('/services/my-services')   // ✅ /api/v1/services/my-services
api.get('/promotions/my-promotions') // ✅ /api/v1/promotions/my-promotions
```

### Fix 2: Update All API Endpoint Calls

**Find and update these endpoint calls in your frontend:**

#### Dashboard Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/dashboard/user')

// ✅ CORRECT:
api.get('/dashboard/user')
```

#### Services Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/services/my-services')

// ✅ CORRECT:
api.get('/services/my-services')
```

#### Promotions Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/promotions/my-promotions')

// ✅ CORRECT:
api.get('/promotions/my-promotions')
```

#### Business Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/business/my-business')

// ✅ CORRECT:
api.get('/business/my-business')
```

#### Listing Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/listing?skip=0&limit=8&category=donations')

// ✅ CORRECT:
api.get('/listing?skip=0&limit=8&category=donations')
```

#### Category Endpoints:
```javascript
// ❌ WRONG:
api.get('/v1/categories/tree')
api.get('/v1/category/education')

// ✅ CORRECT:
api.get('/categories/tree')
api.get('/category/education')
```

### Fix 3: Books API - Add Missing getTrendingGenres Function

**Find your booksAPI service file** and add the missing function:

```javascript
// services/booksAPI.js or similar
class BooksAPI {
  // ... existing functions ...

  async getTrendingGenres() {
    try {
      const response = await api.get('/books-adverts/trending-genres');
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }
}

// OR if using export default:
const getTrendingGenres = async () => {
  try {
    const response = await api.get('/books-adverts/trending-genres');
    return response.data;
  } catch (error) {
    throw error;
  }
};

export { getTrendingGenres };
```

### Fix 4: Error Handling in Redux Slices

**Update CategorySlice.js:**
```javascript
// ❌ CURRENT (causes destructuring error):
const { is_parent } = action.payload.data || {};

// ✅ FIXED:
const { is_parent } = action.payload?.data || {};
```

**Update StoreSlice.js:**
```javascript
// ❌ CURRENT (causes destructuring error):
const { customer_id } = action.payload.data || {};

// ✅ FIXED:
const { customer_id } = action.payload?.data || {};
```

---

## 📋 Complete File-by-File Fix Guide

### 1. API Service Configuration
**File**: `src/services/api.js` (or similar)
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1', // ✅ Base URL with /v1/
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### 2. Books API Service
**File**: `src/services/booksAPI.js`
```javascript
import api from './api';

class BooksAPI {
  async getFeaturedBooks(params = {}) {
    try {
      const response = await api.get('/books-adverts/featured', { params });
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }

  async getStatistics() {
    try {
      const response = await api.get('/books-adverts/statistics');
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }

  async getPricingPlans() {
    try {
      const response = await api.get('/books-adverts/pricing-plans');
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }

  // ✅ ADD MISSING FUNCTION:
  async getTrendingGenres() {
    try {
      const response = await api.get('/books-adverts/trending-genres');
      return response.data;
    } catch (error) {
      this.handleError(error);
    }
  }

  handleError(error) {
    if (error.response?.status === 404) {
      throw new Error('Book endpoint not found');
    }
    throw error;
  }
}

export default new BooksAPI();
```

### 3. Dashboard Service
**File**: `src/services/DashboardService.js`
```javascript
import api from './api';

export const getUserDashboard = async () => {
  try {
    // ✅ FIXED: Removed /v1/ prefix
    const response = await api.get('/dashboard/user');
    return response.data;
  } catch (error) {
    if (error.response?.status === 404) {
      throw new Error('Endpoint not found: /dashboard/user');
    }
    throw error;
  }
};
```

---

## 🚀 Verification Steps

1. **Update API configuration** with correct base URL
2. **Remove all `/v1/` prefixes** from individual endpoint calls
3. **Add missing getTrendingGenres function** to booksAPI
4. **Fix destructuring errors** in Redux slices with optional chaining
5. **Restart frontend development server**
6. **Test in browser console**:
   ```javascript
   // Test health endpoint
   fetch('http://localhost:8000/api/v1/health').then(r => r.json()).then(console.log)
   
   // Test dashboard endpoint (with auth token)
   fetch('http://localhost:8000/api/v1/dashboard/user', {
     headers: { 'Authorization': 'Bearer YOUR_TOKEN' }
   }).then(r => r.json()).then(console.log)
   ```

---

## 📊 Expected Results After Fixes

### ✅ Working Endpoints:
- `/api/v1/health` (200 OK)
- `/api/v1/dashboard/user` (200 with auth)
- `/api/v1/services/my-services` (200 with auth)
- `/api/v1/promotions/my-promotions` (200 with auth)
- `/api/v1/books-adverts/featured` (200 OK)
- `/api/v1/books-adverts/statistics` (200 OK)
- `/api/v1/books-adverts/pricing-plans` (200 OK)

### ❌ Eliminated Errors:
- No more `/v1/v1/` duplicate path 404s
- No more "getTrendingGenres is not a function"
- No more destructuring undefined errors
- Reduced 401 unauthorized errors (fix token handling)

---

## 🔍 Troubleshooting

### Still Getting 404s?
1. Check browser Network tab for exact URLs
2. Verify backend is running on port 8000
3. Check Laravel routes: `php artisan route:list --path=api`

### Still Getting Function Errors?
1. Verify booksAPI import/export syntax
2. Check function names match exactly
3. Ensure API service is properly instantiated

### Still Getting Auth Errors?
1. Verify token is stored in localStorage
2. Check Authorization header format: `Bearer token`
3. Verify token isn't expired

---

## 🎯 Priority Order

1. **HIGH**: Fix `/v1/v1/` duplication (breaks most endpoints)
2. **HIGH**: Add getTrendingGenres function (breaks books page)
3. **MEDIUM**: Fix destructuring errors (improves error handling)
4. **LOW**: Optimize other API calls and error handling

Apply these fixes systematically and test each change before proceeding to the next.
