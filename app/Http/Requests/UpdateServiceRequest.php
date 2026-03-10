<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'sometimes|required|string|min:50',
            'whats_included' => 'nullable|array',
            'whats_included.*' => 'string|max:255',
            'whats_not_included' => 'nullable|array',
            'whats_not_included.*' => 'string|max:255',
            'requirements' => 'nullable|string|max:1000',
            'starting_price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'delivery_time' => 'nullable|integer|min:1|max:365',
            'availability' => 'nullable|array',
            'availability.monday' => 'boolean',
            'availability.tuesday' => 'boolean',
            'availability.wednesday' => 'boolean',
            'availability.thursday' => 'boolean',
            'availability.friday' => 'boolean',
            'availability.saturday' => 'boolean',
            'availability.sunday' => 'boolean',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'service_area_radius' => 'nullable|integer|min:0|max:1000',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Service title is required.',
            'title.max' => 'Service title must not exceed 255 characters.',
            'description.required' => 'Service description is required.',
            'description.min' => 'Service description must be at least 50 characters.',
            'starting_price.required' => 'Starting price is required.',
            'starting_price.numeric' => 'Starting price must be a valid number.',
            'starting_price.min' => 'Starting price must be at least 0.',
            'country.required' => 'Country is required.',
        ];
    }
}
