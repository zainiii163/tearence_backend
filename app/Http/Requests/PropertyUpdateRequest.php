<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Basic Information
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'category' => 'sometimes|required|in:buy,rent,lease,auction,invest',
            'property_type' => 'sometimes|required|in:residential,commercial,industrial,land,agricultural,luxury,short_term_rental,investment,new_development',
            'category_id' => 'nullable|exists:ea_categories,id',
            
            // Location
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'region' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'show_exact_location' => 'boolean',
            
            // Residential Specifications
            'bedrooms' => 'nullable|integer|min:0|max:50',
            'bathrooms' => 'nullable|integer|min:0|max:50',
            'property_size' => 'nullable|numeric|min:0|max:999999.99',
            'size_unit' => 'in:sq_m,sq_ft',
            'furnished' => 'boolean',
            'parking_spaces' => 'nullable|integer|min:0|max:100',
            
            // Commercial Specifications
            'commercial_type' => 'nullable|in:office,retail,warehouse,industrial,restaurant,showroom',
            'floor_area' => 'nullable|numeric|min:0|max:999999.99',
            'footfall_rating' => 'nullable|in:low,medium,high',
            'accessibility_features' => 'boolean',
            
            // Industrial Specifications
            'zoning_type' => 'nullable|string|max:100',
            'warehouse_size' => 'nullable|numeric|min:0|max:999999.99',
            'loading_bays' => 'nullable|integer|min:0|max:100',
            'power_capacity' => 'nullable|numeric|min:0|max:999999.99',
            'ceiling_height' => 'nullable|numeric|min:0|max:99.99',
            
            // Land Specifications
            'land_size' => 'nullable|numeric|min:0|max:9999999.99',
            'land_type' => 'nullable|in:residential,commercial,agricultural',
            'planning_permission' => 'nullable|in:approved,pending,none',
            'soil_quality' => 'nullable|string|max:500',
            
            // Luxury Specifications
            'premium_features' => 'nullable|array',
            'premium_features.*' => 'string|max:100',
            'security_features' => 'nullable|array',
            'security_features.*' => 'string|max:100',
            'view_type' => 'nullable|in:sea,mountain,skyline,garden,pool',
            
            // Investment Specifications
            'rental_yield' => 'nullable|numeric|min:0|max:100',
            'occupancy_rate' => 'nullable|numeric|min:0|max:100',
            'current_rental_income' => 'nullable|numeric|min:0|max:999999999.99',
            'roi_percentage' => 'nullable|numeric|min:0|max:100',
            
            // Pricing
            'price' => 'sometimes|required|numeric|min:0|max:999999999.99',
            'currency' => 'sometimes|required|string|size:3',
            'negotiable' => 'boolean',
            'deposit_required' => 'nullable|numeric|min:0|max:999999999.99',
            'service_charges' => 'nullable|numeric|min:0|max:999999.99',
            'maintenance_fees' => 'nullable|numeric|min:0|max:999999.99',
            
            // Media
            'cover_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'additional_images' => 'nullable|array|max:10',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_tour_link' => 'nullable|url|max:500',
            
            // Description
            'description' => 'nullable|string|max:10000',
            'overview' => 'nullable|string|max:1000',
            'key_features' => 'nullable|string|max:2000',
            'location_highlights' => 'nullable|string|max:2000',
            'nearby_amenities' => 'nullable|string|max:2000',
            'transport_links' => 'nullable|string|max:2000',
            'additional_notes' => 'nullable|string|max:2000',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            
            // Seller/Agent Information
            'seller_name' => 'sometimes|required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'sometimes|required|string|max:50',
            'seller_email' => 'sometimes|required|email|max:255',
            'seller_website' => 'nullable|url|max:500',
            'seller_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'verified_agent' => 'boolean',
            
            // Status
            'status' => 'nullable|in:draft,active,inactive,sold,rented',
        ];

        // Conditional validation based on property type
        if ($this->input('property_type') === 'residential') {
            $rules['bedrooms'] = 'sometimes|required|integer|min:0|max:50';
            $rules['bathrooms'] = 'sometimes|required|integer|min:0|max:50';
            $rules['property_size'] = 'sometimes|required|numeric|min:0|max:999999.99';
        }

        if ($this->input('property_type') === 'commercial') {
            $rules['commercial_type'] = 'sometimes|required|in:office,retail,warehouse,industrial,restaurant,showroom';
            $rules['floor_area'] = 'sometimes|required|numeric|min:0|max:999999.99';
        }

        if ($this->input('property_type') === 'industrial') {
            $rules['zoning_type'] = 'sometimes|required|string|max:100';
            $rules['warehouse_size'] = 'sometimes|required|numeric|min:0|max:999999.99';
        }

        if ($this->input('property_type') === 'land') {
            $rules['land_size'] = 'sometimes|required|numeric|min:0|max:9999999.99';
            $rules['land_type'] = 'sometimes|required|in:residential,commercial,agricultural';
        }

        if ($this->input('property_type') === 'investment') {
            $rules['rental_yield'] = 'sometimes|required|numeric|min:0|max:100';
            $rules['roi_percentage'] = 'sometimes|required|numeric|min:0|max:100';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required.',
            'title.max' => 'Property title may not be greater than 255 characters.',
            'category.required' => 'Property category is required.',
            'category.in' => 'Invalid property category selected.',
            'property_type.required' => 'Property type is required.',
            'property_type.in' => 'Invalid property type selected.',
            'country.required' => 'Country is required.',
            'city.required' => 'City is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'currency.required' => 'Currency is required.',
            'currency.size' => 'Currency must be a 3-character code.',
            'seller_name.required' => 'Seller name is required.',
            'seller_phone.required' => 'Seller phone number is required.',
            'seller_email.required' => 'Seller email is required.',
            'seller_email.email' => 'Please provide a valid email address.',
            'cover_image.required' => 'Cover image is required.',
            'cover_image.image' => 'Cover image must be an image file.',
            'cover_image.mimes' => 'Cover image must be a jpeg, png, jpg, gif, or webp file.',
            'cover_image.max' => 'Cover image may not be larger than 5MB.',
            'additional_images.max' => 'You may upload up to 10 additional images.',
            'additional_images.*.image' => 'Additional images must be image files.',
            'additional_images.*.mimes' => 'Additional images must be jpeg, png, jpg, gif, or webp files.',
            'additional_images.*.max' => 'Additional images may not be larger than 5MB each.',
            'video_tour_link.url' => 'Video tour link must be a valid URL.',
            'bedrooms.required' => 'Number of bedrooms is required for residential properties.',
            'bathrooms.required' => 'Number of bathrooms is required for residential properties.',
            'property_size.required' => 'Property size is required for residential properties.',
            'commercial_type.required' => 'Commercial type is required for commercial properties.',
            'floor_area.required' => 'Floor area is required for commercial properties.',
            'zoning_type.required' => 'Zoning type is required for industrial properties.',
            'warehouse_size.required' => 'Warehouse size is required for industrial properties.',
            'land_size.required' => 'Land size is required for land properties.',
            'land_type.required' => 'Land type is required for land properties.',
            'rental_yield.required' => 'Rental yield is required for investment properties.',
            'roi_percentage.required' => 'ROI percentage is required for investment properties.',
        ];
    }
}
