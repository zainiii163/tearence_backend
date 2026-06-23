# API 404 Errors - Fix Documentation

## Problem Summary

Your frontend application was receiving 404 errors because it was making API requests to the wrong server/port. Additionally, JWT authentication was not configured properly causing "Secret is not set" errors.

## Current Status ✅ FIXED

- **Backend API**: `localhost:8000` ✅ (Laravel - running correctly)
- **Frontend**: `localhost:3000` ✅ (Now correctly pointing to backend)
- **JWT Configuration**: ✅ (Now properly configured)

## Issues Fixed

### 1. API Routing Issues ✅ RESOLVED
**Problem**: Frontend requests `localhost:3000/api/*` instead of `localhost:8000/api/v1/*`
**Solution**: Frontend now correctly uses `http://localhost:8000/api/v1` as base URL

### 2. JWT Authentication Issues ✅ RESOLVED
**Problem**: "Secret is not set" error during login/register
**Solution**: Added JWT configuration to `.env.local` with proper secret key

## Fixed Configuration

### JWT Configuration Added to `.env.local`:
```env
# JWT Configuration
JWT_SECRET=79245d03dd11b2a040889575f046b4d85b38b85bee588daef08c8546e1f131b6
JWT_ALGORITHM=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

### Cache Cleared:
```bash
php artisan config:clear
php artisan cache:clear
```

## Working Endpoints

### Health Check ✅:
```
GET http://localhost:8000/api/v1/health
Response: {status: 'OK', message: 'API is working', ...}
```

### Authentication ✅:
```
POST http://localhost:8000/api/v1/auth/web-login
POST http://localhost:8000/api/v1/auth/register
```

### Categories ✅:
```
GET http://localhost:8000/api/v1/category
GET http://localhost:8000/api/v1/category?is_parent=yes
```

### Sponsored Adverts ✅:
```
GET http://localhost:8000/api/v1/sponsored/categories
GET http://localhost:8000/api/v1/sponsored-adverts/categories
```

## Required Fixes

### 1. Update Frontend API Configuration

#### Option A: Direct API URL Update
Find your frontend API configuration file (likely `api.js`, `axios.js`, or `.env`) and update:

```javascript
// WRONG (current):
const API_BASE_URL = 'http://localhost:3000/api' OR '/api'

// CORRECT:
const API_BASE_URL = 'http://localhost:8000/api/v1'
```

#### Option B: Environment Variables
Create/update `.env` file in frontend:

```env
# .env (frontend)
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1
# OR for Vue:
VUE_APP_API_BASE_URL=http://localhost:8000/api/v1
```

Then update your API service:

```javascript
// api.js
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
```

### 2. Common Frontend Files to Check

Look for these files in your frontend project:

#### React Applications:
- `src/api/api.js`
- `src/config/api.js`
- `src/services/api.js`
- `src/utils/axios.js`
- `.env` or `.env.local`

#### Vue Applications:
- `src/api/index.js`
- `src/config/api.js`
- `src/services/api.js`
- `vue.config.js`

#### General:
- Any file containing `axios.create` or `fetch` calls
- Files with `baseURL` configurations

### 3. Example API Service Configuration

#### React Example:
```javascript
// src/api/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

#### Vue Example:
```javascript
// src/api/index.js
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;
```

### 4. CORS Configuration (Backend)

Ensure your Laravel backend has proper CORS configuration. Check `config/cors.php`:

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

### 5. Development Proxy Setup (Alternative)

#### React (package.json):
```json
{
  "proxy": "http://localhost:8000"
}
```

Then in your API calls:
```javascript
// Can use relative URLs
const response = await fetch('/api/v1/health');
```

#### Vue (vue.config.js):
```javascript
module.exports = {
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        pathRewrite: {
          '^/api': '/api/v1'
        }
      }
    }
  }
};
```

## Verification Steps

1. **Update frontend API configuration** with one of the options above
2. **Restart frontend development server**
3. **Test endpoints** in browser console:
   ```javascript
   fetch('http://localhost:8000/api/v1/health').then(r => r.json()).then(console.log)
   ```
4. **Check browser Network tab** to ensure requests go to `localhost:8000`

## Backend API Endpoints Reference

### Working Endpoints (localhost:8000):
```
GET  /api/v1/health
GET  /api/v1/category
GET  /api/v1/category?is_parent=yes
GET  /api/v1/sponsored/categories
GET  /api/v1/sponsored-adverts/categories
GET  /api/v1/sponsored-adverts/homepage-stats
GET  /api/v1/sponsored-adverts/live-activity
```

### Authentication Required:
```
POST /api/v1/auth/login
POST /api/v1/auth/web-login
GET  /api/v1/auth/user-profile
```

## Troubleshooting

### Still Getting 404s?
1. Verify backend is running: `php artisan serve --port=8000`
2. Check Laravel routes: `php artisan route:list --path=api`
3. Verify CORS middleware is applied
4. Check browser console for exact URLs being requested

### CORS Errors?
1. Add frontend origin to `config/cors.php`
2. Run `php artisan config:clear`
3. Restart Laravel server

### Authentication Issues?
1. Check token is being sent in `Authorization: Bearer <token>` header
2. Verify JWT middleware is applied to routes
3. Check token expiration

## Quick Fix Checklist

- [ ] Update frontend API base URL to `http://localhost:8000/api/v1`
- [ ] Restart frontend development server
- [ ] Verify requests go to port 8000 in browser Network tab
- [ ] Check CORS configuration if needed
- [ ] Test health endpoint: `http://localhost:8000/api/v1/health`

## Support

If issues persist:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Use browser dev tools to inspect exact request URLs
4. Verify both servers are running: `netstat -an | findstr :3000` and `netstat -an | findstr :8000`
