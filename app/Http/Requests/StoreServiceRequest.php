<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:ea_service_categories,id',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'required|string|min:50',
            'whats_included' => 'nullable|array',
            'whats_included.*' => 'string|max:255',
            'whats_not_included' => 'nullable|array',
            'whats_not_included.*' => 'string|max:255',
            'requirements' => 'nullable|string|max:1000',
            'service_type' => 'required|in:freelance,local,business',
            'starting_price' => 'required|numeric|min:0|max:999999.99',
            'currency' => 'required|string|size:3|in:USD,GBP,EUR,JPY,AUD,CAD',
            'delivery_time' => 'nullable|integer|min:1|max:365',
            'availability' => 'nullable|array',
            'availability.monday' => 'boolean',
            'availability.tuesday' => 'boolean',
            'availability.wednesday' => 'boolean',
            'availability.thursday' => 'boolean',
            'availability.friday' => 'boolean',
            'availability.saturday' => 'boolean',
            'availability.sunday' => 'boolean',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'service_area_radius' => 'nullable|integer|min:0|max:1000',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'packages' => 'nullable|array|max:5',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.description' => 'required|string|max:1000',
            'packages.*.price' => 'required|numeric|min:0|max:999999.99',
            'packages.*.delivery_time' => 'required|integer|min:1|max:365',
            'packages.*.features' => 'nullable|array|max:10',
            'packages.*.features.*' => 'string|max:255',
            'packages.*.revisions' => 'nullable|integer|min:0|max:20',
            'packages.*.sort_order' => 'nullable|integer|min:0|max:10',
            'promotion_type' => 'nullable|in:standard,promoted,featured,sponsored,network_boost',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a category for your service.',
            'category_id.exists' => 'The selected category is invalid.',
            'title.required' => 'Service title is required.',
            'title.max' => 'Service title must not exceed 255 characters.',
            'description.required' => 'Service description is required.',
            'description.min' => 'Service description must be at least 50 characters.',
            'service_type.required' => 'Please select a service type.',
            'service_type.in' => 'Invalid service type selected.',
            'starting_price.required' => 'Starting price is required.',
            'starting_price.numeric' => 'Starting price must be a valid number.',
            'starting_price.min' => 'Starting price must be at least 0.',
            'currency.required' => 'Currency is required.',
            'currency.in' => 'Invalid currency selected.',
            'country.required' => 'Country is required.',
            'packages.max' => 'You can add up to 5 packages only.',
            'packages.*.name.required' => 'Package name is required.',
            'packages.*.price.required' => 'Package price is required.',
            'packages.*.delivery_time.required' => 'Package delivery time is required.',
        ];
    }
}
