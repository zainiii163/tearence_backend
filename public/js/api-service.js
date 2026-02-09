class APIService {
    constructor() {
        this.baseURL = window.location.origin + '/api/v1';
        this.token = localStorage.getItem('auth_token');
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }

    removeToken() {
        this.token = null;
        localStorage.removeItem('auth_token');
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        };

        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                // Handle 401 errors gracefully
                if (response.status === 401) {
                    console.log('Authentication failed - token may be expired');
                    // Don't automatically redirect, let the calling code handle it
                    throw new Error('Authentication failed');
                }
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Authentication endpoints
    async register(userData) {
        return this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }

    async login(credentials) {
        const response = await this.request('/auth/web-login', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });
        
        if (response.data.access_token) {
            this.setToken(response.data.access_token);
        }
        
        return response;
    }

    async loginAdmin(credentials) {
        return this.request('/auth/login-admin', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });
    }

    async forgotPassword(email) {
        return this.request('/auth/forgot-password', {
            method: 'POST',
            body: JSON.stringify({ email })
        });
    }

    async resetPassword(resetData) {
        return this.request('/auth/reset-password', {
            method: 'POST',
            body: JSON.stringify(resetData)
        });
    }

    async getUserProfile() {
        return this.request('/auth/user-profile', {
            method: 'GET'
        });
    }

    async changePassword(passwordData) {
        return this.request('/auth/change-password', {
            method: 'POST',
            body: JSON.stringify(passwordData)
        });
    }

    async logout() {
        const response = await this.request('/auth/logout', {
            method: 'GET'
        });
        this.removeToken();
        return response;
    }

    // Listing endpoints
    async getListings(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/listing${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async getListingBySlug(slug) {
        return this.request(`/listing/${slug}`, {
            method: 'GET'
        });
    }

    async getMyListings(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/listing/my-listing${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async createListing(listingData) {
        // Check if listingData is FormData (for file uploads)
        if (listingData instanceof FormData) {
            return this.request('/listing', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                    // Don't set Content-Type for FormData - browser sets it with boundary
                },
                body: listingData
            });
        } else {
            // Regular JSON data
            return this.request('/listing', {
                method: 'POST',
                body: JSON.stringify(listingData)
            });
        }
    }

    async updateListing(id, listingData) {
        return this.request(`/listing/${id}`, {
            method: 'PUT',
            body: JSON.stringify(listingData)
        });
    }

    async deleteListing(id) {
        return this.request(`/listing/${id}`, {
            method: 'DELETE'
        });
    }

    async getFeaturedListings() {
        return this.request('/listing/featured', {
            method: 'POST'
        });
    }

    async getNewListings() {
        return this.request('/listing/new', {
            method: 'POST'
        });
    }

    // Search endpoints
    async searchListings(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/search/listings${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    // Upsell endpoints
    async getUpsellOptions() {
        return this.request('/upsell/options', {
            method: 'GET'
        });
    }

    async purchaseUpsell(upsellData) {
        return this.request('/upsell/purchase', {
            method: 'POST',
            body: JSON.stringify(upsellData)
        });
    }

    async getMyUpsells(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/upsell/my-upsells${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async cancelUpsell(id) {
        return this.request(`/upsell/${id}`, {
            method: 'DELETE'
        });
    }

    // Ad management endpoints
    async getAds(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`/ad${queryString ? '?' + queryString : ''}`, {
            method: 'GET'
        });
    }

    async createAd(adData) {
        return this.request('/ad', {
            method: 'POST',
            body: JSON.stringify(adData)
        });
    }

    async updateAd(id, adData) {
        return this.request(`/ad/${id}`, {
            method: 'PUT',
            body: JSON.stringify(adData)
        });
    }

    async deleteAd(id) {
        return this.request(`/ad/${id}`, {
            method: 'DELETE'
        });
    }

    // Category endpoints
    async getCategories() {
        return this.request('/category', {
            method: 'GET'
        });
    }

    async getCategoryBySlug(slug) {
        return this.request(`/category/${slug}`, {
            method: 'GET'
        });
    }

    // Location endpoints
    async getLocations() {
        return this.request('/location', {
            method: 'GET'
        });
    }

    // Utility methods
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        return this.request('/upload', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
                // Don't set Content-Type for FormData - browser sets it with boundary
            },
            body: formData
        });
    }

    // Check authentication status
    isAuthenticated() {
        return !!this.token;
    }

    // Get current user info
    async getCurrentUser() {
        if (!this.isAuthenticated()) {
            return null;
        }

        try {
            // Use web-check endpoint for frontend authentication
            const response = await this.request('/auth/web-check', {
                method: 'GET'
            });
            
            // Check if user is authenticated and return user data
            if (response.authenticated && response.user) {
                return response.user;
            }
            
            return null;
        } catch (error) {
            console.error('Error getting current user:', error);
            // Only remove token if it's a 401 error (invalid token)
            if (error.message.includes('401') || error.message.includes('Unauthenticated')) {
                this.removeToken();
            }
            return null;
        }
    }

    // Handle API errors globally
    handleApiError(error) {
        if (error.message.includes('401') || error.message.includes('Unauthenticated')) {
            this.removeToken();
            window.location.href = '/login';
        }
        
        // You can add more error handling here
        console.error('API Error:', error);
        return error;
    }
}

// Export for use in other scripts
window.APIService = APIService;
