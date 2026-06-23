<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SponsoredAdvertValidation
{
    /**
     * Validate sponsored advert creation/update data.
     */
    public static function validateSponsoredAdvert(Request $request, $isUpdate = false)
    {
        $rules = [
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'description' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'category' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'country' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'city' => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'price' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'video_url' => 'nullable|url|max:500',
            'advert_type' => $isUpdate ? 'sometimes|required|in:buy,sell,rent,offer,wanted' : 'required|in:buy,sell,rent,offer,wanted',
            'sponsored_tier' => $isUpdate ? 'sometimes|required|in:basic,plus,premium' : 'required|in:basic,plus,premium',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string',
            'seller_info' => 'nullable|array',
            'seller_info.name' => 'nullable|string|max:255',
            'seller_info.phone' => 'nullable|string|max:20',
            'seller_info.email' => 'nullable|email|max:255',
            'location' => 'nullable|array',
            'location.address' => 'nullable|string|max:500',
            'location.latitude' => 'nullable|numeric|between:-90,90',
            'location.longitude' => 'nullable|numeric|between:-180,180',
        ];

        $messages = [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'tagline.max' => 'The tagline may not be greater than 500 characters.',
            'description.required' => 'The description field is required.',
            'category.required' => 'The category field is required.',
            'country.required' => 'The country field is required.',
            'city.required' => 'The city field is required.',
            'price.required' => 'The price field is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'video_url.url' => 'The video URL must be a valid URL.',
            'video_url.max' => 'The video URL may not be greater than 500 characters.',
            'advert_type.required' => 'The advert type field is required.',
            'advert_type.in' => 'The selected advert type is invalid.',
            'sponsored_tier.required' => 'The sponsored tier field is required.',
            'sponsored_tier.in' => 'The selected sponsored tier is invalid.',
            'images.max' => 'You may not upload more than 10 images.',
            'location.latitude.between' => 'The latitude must be between -90 and 90.',
            'location.longitude.between' => 'The longitude must be between -180 and 180.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate inquiry submission.
     */
    public static function validateInquiry(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
            'budget' => 'nullable|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'Your name is required.',
            'email.required' => 'Your email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'message.required' => 'The message field is required.',
            'message.max' => 'The message may not be greater than 1000 characters.',
            'budget.numeric' => 'The budget must be a number.',
            'budget.min' => 'The budget must be at least 0.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate rating submission.
     */
    public static function validateRating(Request $request)
    {
        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'transaction_id' => 'nullable|string|max:255',
        ];

        $messages = [
            'rating.required' => 'The rating field is required.',
            'rating.integer' => 'The rating must be an integer.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating may not be greater than 5.',
            'review.max' => 'The review may not be greater than 1000 characters.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Validate file upload.
     */
    public static function validateFileUpload(Request $request)
    {
        $rules = [
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:image,video',
        ];

        $messages = [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is invalid.',
            'file.max' => 'The file size may not be greater than 10MB.',
            'type.required' => 'The file type is required.',
            'type.in' => 'The file type must be either image or video.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Get allowed categories for sponsored adverts.
     */
    public static function getAllowedCategories()
    {
        return [
            'technology',
            'business',
            'real-estate',
            'vehicles',
            'fashion',
            'education',
            'health-fitness',
            'travel-tourism',
            'food-dining',
            'entertainment',
            'home-garden',
            'sports-recreation',
            'professional-services',
            'shopping',
            'jobs-employment',
        ];
    }

    /**
     * Get pricing tiers configuration.
     */
    public static function getPricingTiers()
    {
        return [
            'basic' => [
                'name' => 'Basic',
                'price' => 29.99,
                'duration_days' => 30,
                'visibility_multiplier' => 3,
                'features' => [
                    'Sponsored Page Only',
                    'Sponsored Badge',
                    'Basic Analytics',
                    'Email Support',
                    'Highlighted advert card'
                ]
            ],
            'plus' => [
                'name' => 'Plus',
                'price' => 59.99,
                'duration_days' => 60,
                'visibility_multiplier' => 5,
                'features' => [
                    'Top of Category',
                    'Plus Badge',
                    'Advanced Analytics',
                    'Priority Email Support',
                    'All Basic features',
                    'Larger advert card',
                    'Priority search ranking',
                    'Weekly Highlights email'
                ]
            ],
            'premium' => [
                'name' => 'Premium',
                'price' => 99.99,
                'duration_days' => 90,
                'visibility_multiplier' => 10,
                'features' => [
                    'Homepage & Top of Category',
                    'Premium VIP Badge',
                    'Real-time Analytics & Insights',
                    'Dedicated Account Manager',
                    'All Plus features',
                    'Homepage placement',
                    'Homepage slider inclusion',
                    'Social media promotion'
                ]
            ]
        ];
    }
}
