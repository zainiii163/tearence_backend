<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerAdUpdateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            // Business Information
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'business_name' => 'sometimes|required|string|max:255',
            'contact_person' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'website_url' => 'sometimes|nullable|url|max:500',
            'business_logo' => 'sometimes|nullable|string|max:255',
            
            // Banner Details
            'banner_type' => ['sometimes', 'required', Rule::in(['image', 'animated', 'html5', 'video'])],
            'banner_size' => ['sometimes', 'required', Rule::in(['728x90', '300x250', '160x600', '970x250', '468x60', '1080x1080'])],
            'banner_image' => 'sometimes|required|string|max:255',
            'destination_link' => 'sometimes|required|url|max:500',
            'call_to_action' => 'sometimes|nullable|string|max:100',
            'key_selling_points' => 'sometimes|nullable|string|max:1000',
            'offer_details' => 'sometimes|nullable|string|max:1000',
            'validity_start' => 'sometimes|nullable|date',
            'validity_end' => 'sometimes|nullable|date|after_or_equal:validity_start',
            
            // Category and Location
            'banner_category_id' => 'sometimes|required|exists:ea_banner_categories,id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'target_countries' => 'sometimes|nullable|array',
            'target_countries.*' => 'string|max:100',
            'target_audience' => 'sometimes|nullable|array',
            'target_audience.*' => 'string|max:255',
            
            // Promotion and Pricing
            'promotion_tier' => ['sometimes', 'required', Rule::in(['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_price' => 'sometimes|required|numeric|min:0',
            'promotion_start' => 'sometimes|nullable|date',
            'promotion_end' => 'sometimes|nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'sometimes|boolean',
            
            // Status
            'status' => ['sometimes', Rule::in(['draft', 'pending', 'active', 'rejected', 'expired'])],
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'banner_type.required' => 'Please select a banner type.',
            'banner_type.in' => 'Invalid banner type selected.',
            'banner_size.required' => 'Please select a banner size.',
            'banner_size.in' => 'Invalid banner size selected.',
            'banner_category_id.required' => 'Please select a category.',
            'banner_category_id.exists' => 'Selected category does not exist.',
            'country.required' => 'Country is required.',
            'destination_link.required' => 'Destination link is required.',
            'destination_link.url' => 'Please provide a valid URL.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'promotion_tier.required' => 'Please select a promotion tier.',
            'promotion_tier.in' => 'Invalid promotion tier selected.',
            'validity_end.after_or_equal' => 'End date must be after or equal to start date.',
            'promotion_end.after_or_equal' => 'Promotion end date must be after or equal to start date.',
        ];
    }
}
