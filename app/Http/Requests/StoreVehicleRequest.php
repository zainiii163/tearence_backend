<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'business_id' => 'nullable|exists:businesses,id',
            'category_id' => 'required|exists:vehicle_categories,id',
            'make_id' => 'required|exists:vehicle_makes,id',
            'model_id' => 'required|exists:vehicle_models,id',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'advert_type' => 'required|in:sale,hire,lease,transport_service',
            'condition' => 'required|in:new,used,excellent,good,fair',
            
            // Vehicle Specifications
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'nullable|integer|min:0',
            'fuel_type' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'engine_size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'doors' => 'nullable|integer|min:1|max:10',
            'seats' => 'nullable|integer|min:1|max:20',
            'body_type' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:17',
            'registration_number' => 'nullable|string|max:50',
            
            // Commercial Vehicle Specific
            'payload_capacity' => 'nullable|numeric|min:0',
            'axles' => 'nullable|integer|min:1|max:10',
            'emission_class' => 'nullable|string|max:20',
            
            // Boat Specific
            'length' => 'nullable|numeric|min:0',
            'engine_type' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'trailer_included' => 'boolean',
            
            // Transport Service Specific
            'service_area' => 'nullable|string',
            'operating_hours' => 'nullable|string|max:100',
            'passenger_capacity' => 'nullable|integer|min:1',
            'luggage_capacity' => 'nullable|integer|min:0',
            'airport_pickup' => 'boolean',
            
            // Pricing
            'price' => 'nullable|numeric|min:0',
            'price_type' => 'required|in:fixed,per_day,per_week,per_month,per_hour',
            'negotiable' => 'boolean',
            'deposit' => 'nullable|numeric|min:0',
            
            // Media
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'array|max:15',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_link' => 'nullable|url|max:255',
            
            // Location
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'show_exact_location' => 'boolean',
            
            // Contact
            'contact_name' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'website' => 'nullable|url|max:255',
            
            // Additional
            'features' => 'array',
            'features.*' => 'string|max:100',
            'service_history' => 'nullable|string',
            'mot_expiry' => 'nullable|date',
            'road_tax_status' => 'nullable|string|max:50',
            'previous_owners' => 'nullable|integer|min:0',
            
            // Upgrades
            'upgrade_type' => 'nullable|in:promoted,featured,sponsored,top_of_category',
            'pricing_plan_id' => 'nullable|exists:ad_pricing_plans,id',
            'duration_days' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a vehicle category.',
            'category_id.exists' => 'Selected vehicle category is invalid.',
            'make_id.required' => 'Please select a vehicle make.',
            'make_id.exists' => 'Selected vehicle make is invalid.',
            'model_id.required' => 'Please select a vehicle model.',
            'model_id.exists' => 'Selected vehicle model is invalid.',
            'advert_type.required' => 'Please select an advert type.',
            'advert_type.in' => 'Invalid advert type selected.',
            'title.required' => 'Vehicle title is required.',
            'title.max' => 'Vehicle title cannot exceed 255 characters.',
            'condition.required' => 'Vehicle condition is required.',
            'year.required' => 'Vehicle year is required.',
            'year.min' => 'Year must be 1900 or later.',
            'year.max' => 'Year cannot be more than next year.',
            'price_type.required' => 'Price type is required.',
            'country.required' => 'Country is required.',
            'city.required' => 'City is required.',
            'main_image.required' => 'Main vehicle image is required.',
            'main_image.image' => 'Main image must be an image file.',
            'additional_images.max' => 'You can upload maximum 15 additional images.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
