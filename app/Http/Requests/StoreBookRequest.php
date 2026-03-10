<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'required|string|min:50',
            'short_description' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'currency' => 'nullable|string|size:3|in:USD,GBP,EUR,JPY,CAD,AUD',
            'book_type' => ['required', 'string', Rule::in(['fiction', 'non-fiction', 'children', 'poetry', 'academic', 'self-help', 'business', 'other'])],
            'genre' => 'required|string|max:100',
            'author_name' => 'required|string|max:255',
            'author_id' => 'nullable|integer|exists:ea_authors,id',
            'country' => 'required|string|size:2|exists:ea_countries,code',
            'language' => 'required|string|max:10',
            'format' => ['required', 'string', Rule::in(['paperback', 'hardcover', 'ebook', 'audiobook'])],
            'isbn' => 'nullable|string|max:20|regex:/^[0-9Xx\-]+$/',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date|before_or_equal:today',
            'pages' => 'nullable|integer|min:1|max:9999',
            'age_range' => 'nullable|string|max:50',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'purchase_links' => 'nullable|array|max:10',
            'purchase_links.*.platform' => 'required|string|max:100',
            'purchase_links.*.url' => 'required|url|max:500',
            'trailer_video_url' => 'nullable|url|max:500',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array|max:10',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'sample_files' => 'nullable|array|max:5',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240', // 10MB max
            'upsell_type' => ['nullable', 'string', Rule::in(['promoted', 'featured', 'sponsored', 'top_category'])],
            'verified_author_badge' => 'nullable|boolean'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The book title is required.',
            'title.max' => 'The book title may not be greater than 255 characters.',
            'description.required' => 'The book description is required.',
            'description.min' => 'The book description must be at least 50 characters.',
            'book_type.required' => 'Please select a book type.',
            'book_type.in' => 'Invalid book type selected.',
            'genre.required' => 'The genre is required.',
            'author_name.required' => 'The author name is required.',
            'country.required' => 'Please select a country.',
            'country.exists' => 'Invalid country selected.',
            'language.required' => 'The language is required.',
            'format.required' => 'Please select a book format.',
            'format.in' => 'Invalid format selected.',
            'isbn.regex' => 'The ISBN format is invalid.',
            'publication_date.before_or_equal' => 'The publication date cannot be in the future.',
            'pages.min' => 'The number of pages must be at least 1.',
            'purchase_links.*.platform.required' => 'Platform name is required for each purchase link.',
            'purchase_links.*.url.required' => 'URL is required for each purchase link.',
            'purchase_links.*.url.url' => 'Please provide valid URLs for purchase links.',
            'cover_image.required' => 'A cover image is required.',
            'cover_image.image' => 'The cover must be an image file.',
            'cover_image.mimes' => 'The cover must be a JPEG, PNG, JPG, or GIF file.',
            'cover_image.max' => 'The cover image may not be larger than 2MB.',
            'sample_files.*.mimes' => 'Sample files must be PDF, MP3, M4A, or WAV files.',
            'sample_files.*.max' => 'Sample files may not be larger than 10MB.',
            'upsell_type.in' => 'Invalid upsell type selected.',
        ];
    }
}
