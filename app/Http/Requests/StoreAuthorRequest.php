<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAuthorRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'required|email|max:255|unique:ea_authors,email',
            'website' => 'nullable|url|max:500',
            'social_links' => 'nullable|array|max:10',
            'social_links.*.platform' => 'required_with:social_links|string|max:50',
            'social_links.*.url' => 'required_with:social_links|url|max:500',
            'country' => 'required|string|size:2|exists:ea_countries,code',
            'user_id' => 'nullable|integer|exists:ea_users,id'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The author name is required.',
            'name.max' => 'The author name may not be greater than 255 characters.',
            'bio.max' => 'The bio may not be greater than 2000 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'website.url' => 'Please provide a valid website URL.',
            'social_links.*.platform.required_with' => 'Platform name is required for each social link.',
            'social_links.*.url.required_with' => 'URL is required for each social link.',
            'social_links.*.url.url' => 'Please provide valid URLs for social links.',
            'country.required' => 'Please select a country.',
            'country.exists' => 'Invalid country selected.',
            'photo.image' => 'The photo must be an image file.',
            'photo.mimes' => 'The photo must be a JPEG, PNG, JPG, or GIF file.',
            'photo.max' => 'The photo may not be larger than 2MB.',
        ];
    }
}
