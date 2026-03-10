<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUpsellStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:ea_properties,id',
            'upsell_type' => 'required|in:promoted,featured,sponsored',
            'duration_days' => 'required|in:7,14,30',
        ];
    }

    public function messages(): array
    {
        return [
            'property_id.required' => 'Property ID is required.',
            'property_id.exists' => 'Selected property does not exist.',
            'upsell_type.required' => 'Upsell type is required.',
            'upsell_type.in' => 'Invalid upsell type selected.',
            'duration_days.required' => 'Duration is required.',
            'duration_days.in' => 'Duration must be 7, 14, or 30 days.',
        ];
    }
}
