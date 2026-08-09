<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Convert string '1'/'0' to actual booleans for FormData compatibility
        $booleanFields = [
            'show_exact_location', 'furnished', 'accessibility_features',
            'verified_agent', 'negotiable'
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if ($value === '1' || $value === 1 || $value === true) {
                    $this->merge([$field => true]);
                } elseif ($value === '0' || $value === 0 || $value === false) {
                    $this->merge([$field => false]);
                }
            }
        }

        if ($this->input('category') === 'sell') {
            $this->merge(['category' => 'buy']);
        }

        // Decode JSON string fields from multipart FormData
        foreach (['premium_features', 'security_features', 'amenities'] as $jsonField) {
            if ($this->has($jsonField) && is_string($this->input($jsonField))) {
                $decoded = json_decode($this->input($jsonField), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge([$jsonField => $decoded]);
                }
            }
        }
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'category' => 'required|in:buy,rent,lease,auction,invest',
            'property_type' => 'required|in:residential,commercial,industrial,land,agricultural,luxury,short_term_rental,investment,new_development',

            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'show_exact_location' => 'nullable|boolean',

            'bedrooms' => 'nullable|integer|min:0|max:50',
            'bathrooms' => 'nullable|integer|min:0|max:50',
            'property_size' => 'nullable|numeric|min:0|max:999999.99',
            'size_unit' => 'nullable|in:sq_m,sq_ft',
            'furnished' => 'nullable|boolean',
            'parking_spaces' => 'nullable|integer|min:0|max:100',

            'commercial_type' => 'nullable|string|max:100',
            'floor_area' => 'nullable|numeric|min:0|max:999999.99',
            'footfall_rating' => 'nullable|string|max:50',
            'accessibility_features' => 'nullable|boolean',

            'zoning_type' => 'nullable|string|max:100',
            'warehouse_size' => 'nullable|numeric|min:0|max:999999.99',
            'loading_bays' => 'nullable|integer|min:0|max:100',
            'power_capacity' => 'nullable|string|max:100',
            'ceiling_height' => 'nullable|numeric|min:0|max:99.99',

            'land_size' => 'nullable|numeric|min:0|max:9999999.99',
            'land_type' => 'nullable|string|max:100',
            'planning_permission' => 'nullable|string|max:100',
            'soil_quality' => 'nullable|string|max:500',

            'premium_features' => 'nullable',
            'security_features' => 'nullable',
            'view_type' => 'nullable|string|max:100',

            'rental_yield' => 'nullable|numeric|min:0|max:100',
            'occupancy_rate' => 'nullable|numeric|min:0|max:100',
            'current_rental_income' => 'nullable|numeric|min:0|max:999999999.99',
            'roi_percentage' => 'nullable|numeric|min:0|max:100',

            'price' => 'required|numeric|min:0|max:999999999.99',
            'currency' => 'required|string|size:3',
            'negotiable' => 'nullable|boolean',
            'deposit' => 'nullable|numeric|min:0|max:999999999.99',
            'deposit_required' => 'nullable|numeric|min:0|max:999999999.99',
            'service_charges' => 'nullable|numeric|min:0|max:999999.99',
            'maintenance_fees' => 'nullable|numeric|min:0|max:999999.99',

            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'additional_images' => 'nullable|array|max:10',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_tour_link' => 'nullable|string|max:500',

            'description' => 'nullable|string|max:10000',
            'overview' => 'nullable|string|max:5000',
            'key_features' => 'nullable|string|max:5000',
            'location_highlights' => 'nullable|string|max:5000',
            'nearby_amenities' => 'nullable|string|max:5000',
            'transport_links' => 'nullable|string|max:5000',
            'additional_notes' => 'nullable|string|max:5000',
            'amenities' => 'nullable',

            'seller_name' => 'required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'required|string|max:50',
            'seller_email' => 'required|email|max:255',
            'seller_website' => 'nullable|string|max:500',
            'seller_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'verified_agent' => 'nullable|boolean',
            'advert_type' => 'nullable|in:standard,promoted,featured,sponsored',
        ];

        if ($this->input('property_type') === 'residential') {
            $rules['bedrooms'] = 'required|integer|min:0|max:50';
            $rules['bathrooms'] = 'required|integer|min:0|max:50';
            $rules['property_size'] = 'required|numeric|min:0|max:999999.99';
        }

        if ($this->input('property_type') === 'commercial') {
            $rules['commercial_type'] = 'required|string|max:100';
            $rules['floor_area'] = 'required|numeric|min:0|max:999999.99';
        }

        if ($this->input('property_type') === 'industrial') {
            $rules['zoning_type'] = 'required|string|max:100';
            $rules['warehouse_size'] = 'required|numeric|min:0|max:999999.99';
        }

        if (in_array($this->input('property_type'), ['land', 'agricultural'], true)) {
            $rules['land_size'] = 'required|numeric|min:0|max:9999999.99';
            $rules['land_type'] = 'required|string|max:100';
        }

        if ($this->input('property_type') === 'investment') {
            $rules['rental_yield'] = 'required|numeric|min:0|max:100';
            $rules['roi_percentage'] = 'required|numeric|min:0|max:100';
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
