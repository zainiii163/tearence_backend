<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|min:50',
            'short_description' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'currency' => 'nullable|string|size:3|in:USD,GBP,EUR,JPY,CAD,AUD',
            'book_type' => ['nullable', 'string', Rule::in(['fiction', 'non-fiction', 'children', 'poetry', 'academic', 'self-help', 'business', 'other'])],
            'genre' => 'nullable|string|max:100',
            'author_name' => 'nullable|string|max:255',
            'author_id' => 'nullable|integer|exists:ea_authors,id',
            'country' => 'nullable|string|size:2|exists:ea_countries,code',
            'language' => 'nullable|string|max:10',
            'format' => ['nullable', 'string', Rule::in(['paperback', 'hardcover', 'ebook', 'audiobook'])],
            'isbn' => 'nullable|string|max:20|regex:/^[0-9Xx\-]+$/',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date|before_or_equal:today',
            'pages' => 'nullable|integer|min:1|max:9999',
            'age_range' => 'nullable|string|max:50',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'purchase_links' => 'nullable|array|max:10',
            'purchase_links.*.platform' => 'required_with:purchase_links|string|max:100',
            'purchase_links.*.url' => 'required_with:purchase_links|url|max:500',
            'trailer_video_url' => 'nullable|url|max:500',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array|max:10',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'sample_files' => 'nullable|array|max:5',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The book title may not be greater than 255 characters.',
            'description.min' => 'The book description must be at least 50 characters.',
            'book_type.in' => 'Invalid book type selected.',
            'country.exists' => 'Invalid country selected.',
            'format.in' => 'Invalid format selected.',
            'isbn.regex' => 'The ISBN format is invalid.',
            'publication_date.before_or_equal' => 'The publication date cannot be in the future.',
            'pages.min' => 'The number of pages must be at least 1.',
            'purchase_links.*.platform.required_with' => 'Platform name is required for each purchase link.',
            'purchase_links.*.url.required_with' => 'URL is required for each purchase link.',
            'purchase_links.*.url.url' => 'Please provide valid URLs for purchase links.',
            'cover_image.image' => 'The cover must be an image file.',
            'cover_image.mimes' => 'The cover must be a JPEG, PNG, JPG, or GIF file.',
            'cover_image.max' => 'The cover image may not be larger than 2MB.',
            'sample_files.*.mimes' => 'Sample files must be PDF, MP3, M4A, or WAV files.',
            'sample_files.*.max' => 'Sample files may not be larger than 10MB.',
        ];
    }
}
