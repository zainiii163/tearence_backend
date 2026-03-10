<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && $this->route('vehicle')->user_id === auth()->id();
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
            'business_id' => 'sometimes|nullable|exists:businesses,id',
            'category_id' => 'sometimes|required|exists:vehicle_categories,id',
            'make_id' => 'sometimes|required|exists:vehicle_makes,id',
            'model_id' => 'sometimes|required|exists:vehicle_models,id',
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'advert_type' => 'sometimes|required|in:sale,hire,lease,transport_service',
            'condition' => 'sometimes|required|in:new,used,excellent,good,fair',
            
            // Vehicle Specifications
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'sometimes|nullable|integer|min:0',
            'fuel_type' => 'sometimes|nullable|string|max:50',
            'transmission' => 'sometimes|nullable|string|max:50',
            'engine_size' => 'sometimes|nullable|string|max:50',
            'color' => 'sometimes|nullable|string|max:50',
            'doors' => 'sometimes|nullable|integer|min:1|max:10',
            'seats' => 'sometimes|nullable|integer|min:1|max:20',
            'body_type' => 'sometimes|nullable|string|max:50',
            'vin' => 'sometimes|nullable|string|max:17',
            'registration_number' => 'sometimes|nullable|string|max:50',
            
            // Commercial Vehicle Specific
            'payload_capacity' => 'sometimes|nullable|numeric|min:0',
            'axles' => 'sometimes|nullable|integer|min:1|max:10',
            'emission_class' => 'sometimes|nullable|string|max:20',
            
            // Boat Specific
            'length' => 'sometimes|nullable|numeric|min:0',
            'engine_type' => 'sometimes|nullable|string|max:50',
            'capacity' => 'sometimes|nullable|integer|min:1',
            'trailer_included' => 'sometimes|boolean',
            
            // Transport Service Specific
            'service_area' => 'sometimes|nullable|string',
            'operating_hours' => 'sometimes|nullable|string|max:100',
            'passenger_capacity' => 'sometimes|nullable|integer|min:1',
            'luggage_capacity' => 'sometimes|nullable|integer|min:0',
            'airport_pickup' => 'sometimes|boolean',
            
            // Pricing
            'price' => 'sometimes|nullable|numeric|min:0',
            'price_type' => 'sometimes|required|in:fixed,per_day,per_week,per_month,per_hour',
            'negotiable' => 'sometimes|boolean',
            'deposit' => 'sometimes|nullable|numeric|min:0',
            
            // Media
            'main_image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'sometimes|array|max:15',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_link' => 'sometimes|nullable|url|max:255',
            
            // Location
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'address' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'show_exact_location' => 'sometimes|boolean',
            
            // Contact
            'contact_name' => 'sometimes|nullable|string|max:100',
            'contact_phone' => 'sometimes|nullable|string|max:50',
            'contact_email' => 'sometimes|nullable|email|max:100',
            'website' => 'sometimes|nullable|url|max:255',
            
            // Additional
            'features' => 'sometimes|array',
            'features.*' => 'string|max:100',
            'service_history' => 'sometimes|nullable|string',
            'mot_expiry' => 'sometimes|nullable|date',
            'road_tax_status' => 'sometimes|nullable|string|max:50',
            'previous_owners' => 'sometimes|nullable|integer|min:0',
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
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
