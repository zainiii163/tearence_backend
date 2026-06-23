# Vehicles API Frontend Integration Guide

## Quick Start

### Base URL
```
http://127.0.0.1:8000/api/v1
```

### Authentication
```javascript
// Login to get token
const login = async (email, password) => {
  const response = await fetch(`${BASE_URL}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  return data.access_token; // Save this token
};

// Use token in requests
const headers = {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
};
```

---

## Core Endpoints

### 1. Get All Vehicles (Public)
```javascript
const getVehicles = async (filters = {}) => {
  const params = new URLSearchParams(filters);
  const response = await fetch(`${BASE_URL}/vehicles?${params}`);
  return response.json();
};

// Usage examples:
await getVehicles({ page: 1, limit: 10 });
await getVehicles({ category_id: 1, make_id: 1 });
await getVehicles({ search: 'Toyota', sort_by: 'price', sort_order: 'asc' });
await getVehicles({ price_min: 5000, price_max: 20000 });
```

### 2. Get Vehicle Details
```javascript
const getVehicle = async (id) => {
  const response = await fetch(`${BASE_URL}/vehicles/${id}`);
  return response.json();
};
```

### 3. Get Featured Vehicles
```javascript
const getFeaturedVehicles = async () => {
  const response = await fetch(`${BASE_URL}/vehicles/featured`);
  return response.json();
};
```

### 4. Get Recent Vehicles
```javascript
const getRecentVehicles = async () => {
  const response = await fetch(`${BASE_URL}/vehicles/recent`);
  return response.json();
};
```

---

## Data Endpoints

### Get Vehicle Categories
```javascript
const getCategories = async () => {
  const response = await fetch(`${BASE_URL}/vehicles/categories`);
  return response.json();
};
```

### Get Vehicle Makes
```javascript
const getMakes = async (search = '') => {
  const url = search ? `${BASE_URL}/vehicles/makes?search=${search}` : `${BASE_URL}/vehicles/makes`;
  const response = await fetch(url);
  return response.json();
};
```

### Get Models by Make
```javascript
const getModels = async (makeId) => {
  const response = await fetch(`${BASE_URL}/vehicles/models/${makeId}`);
  return response.json();
};
```

---

## Vehicle Management (Authenticated)

### Create Vehicle
```javascript
const createVehicle = async (vehicleData, token) => {
  const formData = new FormData();
  
  // Add all text fields
  Object.keys(vehicleData).forEach(key => {
    if (key !== 'main_image' && key !== 'additional_images') {
      formData.append(key, vehicleData[key]);
    }
  });
  
  // Add images
  if (vehicleData.main_image) {
    formData.append('main_image', vehicleData.main_image);
  }
  
  if (vehicleData.additional_images) {
    vehicleData.additional_images.forEach(image => {
      formData.append('additional_images[]', image);
    });
  }

  const response = await fetch(`${BASE_URL}/vehicles`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
  
  return response.json();
};
```

### Update Vehicle
```javascript
const updateVehicle = async (id, vehicleData, token) => {
  const formData = new FormData();
  
  Object.keys(vehicleData).forEach(key => {
    if (key !== 'main_image' && key !== 'additional_images') {
      formData.append(key, vehicleData[key]);
    }
  });
  
  if (vehicleData.main_image) {
    formData.append('main_image', vehicleData.main_image);
  }
  
  const response = await fetch(`${BASE_URL}/vehicles/${id}`, {
    method: 'PUT',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
  
  return response.json();
};
```

### Delete Vehicle
```javascript
const deleteVehicle = async (id, token) => {
  const response = await fetch(`${BASE_URL}/vehicles/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return response.json();
};
```

---

## User-Specific Endpoints

### Get My Vehicles
```javascript
const getMyVehicles = async (token) => {
  const response = await fetch(`${BASE_URL}/vehicles/my-vehicles`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return response.json();
};
```

### Get Saved Vehicles
```javascript
const getSavedVehicles = async (token) => {
  const response = await fetch(`${BASE_URL}/vehicles/saved`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return response.json();
};
```

### Save/Unsave Vehicle
```javascript
const toggleSaveVehicle = async (id, token) => {
  const response = await fetch(`${BASE_URL}/vehicles/${id}/save`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return response.json();
};
```

---

## Enquiries & Analytics

### Submit Enquiry
```javascript
const submitEnquiry = async (vehicleId, enquiryData, token) => {
  const response = await fetch(`${BASE_URL}/vehicles/${vehicleId}/enquiry`, {
    method: 'POST',
    headers: { 
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(enquiryData)
  });
  return response.json();
};
```

---

## Search & Filtering

### Advanced Search Parameters
```javascript
const searchParams = {
  // Basic filters
  page: 1,
  limit: 20,
  search: 'Toyota Corolla',
  
  // Category/Make/Model
  category_id: 1,
  make_id: 1,
  model_id: 1,
  
  // Vehicle specifics
  advert_type: 'sale', // sale, hire, lease, transport_service
  condition: 'good', // new, used, excellent, good, fair
  fuel_type: 'petrol',
  transmission: 'automatic',
  
  // Price & Year
  price_min: 5000,
  price_max: 20000,
  year_min: 2018,
  year_max: 2022,
  
  // Location
  country: 'USA',
  city: 'New York',
  
  // Special filters
  featured: true,
  promoted: true,
  sponsored: true,
  
  // Sorting
  sort_by: 'price', // created_at, price, year, views, saves
  sort_order: 'asc' // asc, desc
};
```

---

## Data Structures

### Vehicle Object
```json
{
  "id": 1,
  "title": "Toyota Corolla 2020",
  "tagline": "Well maintained family car",
  "description": "Excellent condition...",
  "advert_type": "sale",
  "condition": "good",
  "year": 2020,
  "mileage": 45000,
  "fuel_type": "petrol",
  "transmission": "automatic",
  "engine_size": "1.8L",
  "color": "White",
  "doors": 4,
  "seats": 5,
  "body_type": "sedan",
  "vin": "12345678901234567",
  "registration_number": "ABC-123",
  "price": 15000.00,
  "price_type": "fixed",
  "negotiable": false,
  "deposit": null,
  "country": "USA",
  "city": "New York",
  "address": "123 Main St",
  "latitude": "40.71280000",
  "longitude": "-74.00600000",
  "show_exact_location": true,
  "contact_name": "John Doe",
  "contact_phone": "+1234567890",
  "contact_email": "john@example.com",
  "website": null,
  "features": ["Air Conditioning", "Power Steering"],
  "service_history": "Full service history available",
  "mot_expiry": "2024-12-31",
  "road_tax_status": "Valid",
  "previous_owners": 2,
  "status": "approved",
  "is_active": true,
  "is_promoted": false,
  "is_featured": true,
  "is_sponsored": false,
  "views": 125,
  "clicks": 45,
  "saves": 12,
  "enquiries": 8,
  "main_image": "http://127.0.0.1:8000/storage/vehicles/image1.jpg",
  "additional_images": [
    "http://127.0.0.1:8000/storage/vehicles/image2.jpg"
  ],
  "video_link": "https://youtube.com/watch?v=example",
  "category": {
    "id": 1,
    "name": "Cars",
    "slug": "cars"
  },
  "make": {
    "id": 1,
    "name": "Toyota",
    "slug": "toyota"
  },
  "model": {
    "id": 1,
    "name": "Corolla",
    "slug": "corolla"
  },
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "created_at": "2026-03-28T07:00:00.000000Z",
  "updated_at": "2026-03-28T07:00:00.000000Z"
}
```

### Paginated Response
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 150,
    "last_page": 15,
    "from": 1,
    "to": 10
  }
}
```

---

## Error Handling

### Standard Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "title": ["The title field is required."],
      "price": ["The price must be a number."]
    }
  }
}
```

### Error Handling Function
```javascript
const handleApiError = (error) => {
  if (error.response) {
    const errorData = error.response.data;
    
    switch (error.response.status) {
      case 401:
        // Redirect to login
        window.location.href = '/login';
        break;
      case 403:
        // Show permission denied message
        alert('You do not have permission to perform this action');
        break;
      case 422:
        // Validation errors
        if (errorData.error.details) {
          Object.entries(errorData.error.details).forEach(([field, messages]) => {
            console.error(`${field}: ${messages.join(', ')}`);
          });
        }
        break;
      case 500:
        // Server error
        alert('Server error. Please try again later.');
        break;
      default:
        console.error('API Error:', errorData);
    }
  } else {
    console.error('Network Error:', error);
  }
};
```

---

## Frontend Integration Examples

### React Component Example
```jsx
import React, { useState, useEffect } from 'react';

const VehicleList = () => {
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    page: 1,
    limit: 12,
    category_id: '',
    make_id: '',
    search: ''
  });

  useEffect(() => {
    fetchVehicles();
  }, [filters]);

  const fetchVehicles = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams(filters);
      const response = await fetch(`/api/v1/vehicles?${params}`);
      const data = await response.json();
      setVehicles(data.data);
    } catch (error) {
      handleApiError(error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value, page: 1 }));
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      {/* Search and filters */}
      <input
        type="text"
        placeholder="Search vehicles..."
        value={filters.search}
        onChange={(e) => handleFilterChange('search', e.target.value)}
      />
      
      {/* Vehicle list */}
      <div className="vehicle-grid">
        {vehicles.map(vehicle => (
          <VehicleCard key={vehicle.id} vehicle={vehicle} />
        ))}
      </div>
    </div>
  );
};
```

### Vue.js Component Example
```vue
<template>
  <div>
    <div class="filters">
      <input v-model="filters.search" @input="fetchVehicles" placeholder="Search...">
      <select v-model="filters.category_id" @change="fetchVehicles">
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
          {{ cat.name }}
        </option>
      </select>
    </div>
    
    <div v-if="loading" class="loading">Loading...</div>
    
    <div v-else class="vehicle-grid">
      <div v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-card">
        <img :src="vehicle.main_image" :alt="vehicle.title">
        <h3>{{ vehicle.title }}</h3>
        <p class="price">${{ vehicle.price }}</p>
        <p class="location">{{ vehicle.city }}, {{ vehicle.country }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      vehicles: [],
      categories: [],
      loading: false,
      filters: {
        page: 1,
        limit: 12,
        search: '',
        category_id: ''
      }
    };
  },
  
  async mounted() {
    await this.fetchCategories();
    await this.fetchVehicles();
  },
  
  methods: {
    async fetchVehicles() {
      try {
        this.loading = true;
        const params = new URLSearchParams(this.filters);
        const response = await fetch(`/api/v1/vehicles?${params}`);
        const data = await response.json();
        this.vehicles = data.data;
      } catch (error) {
        this.handleApiError(error);
      } finally {
        this.loading = false;
      }
    },
    
    async fetchCategories() {
      const response = await fetch('/api/v1/vehicles/categories');
      const data = await response.json();
      this.categories = data.data;
    }
  }
};
</script>
```

---

## File Upload Guidelines

### Image Upload
```javascript
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  
  // Validate file
  if (!file) return;
  
  if (!file.type.match('image.*')) {
    alert('Please select an image file');
    return;
  }
  
  if (file.size > 2 * 1024 * 1024) { // 2MB
    alert('Image size must be less than 2MB');
    return;
  }
  
  return file;
};
```

### Multiple Images
```javascript
const handleMultipleImages = (event) => {
  const files = Array.from(event.target.files);
  
  // Validate each file
  const validFiles = files.filter(file => {
    return file.type.match('image.*') && file.size <= 2 * 1024 * 1024;
  });
  
  if (validFiles.length !== files.length) {
    alert('Some files were invalid. Only images under 2MB are allowed.');
  }
  
  return validFiles;
};
```

---

## Performance Tips

1. **Pagination**: Always use pagination for large datasets
2. **Image Optimization**: Compress images before upload
3. **Caching**: Cache categories, makes, and models data
4. **Lazy Loading**: Implement lazy loading for vehicle images
5. **Debounced Search**: Debounce search input to reduce API calls

```javascript
// Debounced search example
const useDebounce = (value, delay) => {
  const [debouncedValue, setDebouncedValue] = useState(value);
  
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);
    
    return () => clearTimeout(handler);
  }, [value, delay]);
  
  return debouncedValue;
};
```

---

## Testing

### Testing with Postman
1. Import the `VEHICLES_API_COLLECTION.json` file into Postman
2. Set the `base_url` and `auth_token` environment variables
3. Test endpoints using the provided collection

### Testing with cURL
```bash
# Get all vehicles
curl -X GET "http://127.0.0.1:8000/api/v1/vehicles?page=1&limit=10"

# Get vehicle details
curl -X GET "http://127.0.0.1:8000/api/v1/vehicles/1"

# Create vehicle (with auth token)
curl -X POST "http://127.0.0.1:8000/api/v1/vehicles" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Vehicle","category_id":1,"make_id":1,"model_id":1}'
```

---

## Rate Limiting

- **Public endpoints**: 100 requests per hour
- **Authenticated endpoints**: 1000 requests per hour
- **File uploads**: 10 requests per minute

Implement proper error handling and retry logic for rate-limited requests.

---

## Support

For API issues or questions:
1. Check the error messages and logs
2. Test with the Postman collection
3. Verify authentication tokens are valid
4. Ensure proper request format and headers
