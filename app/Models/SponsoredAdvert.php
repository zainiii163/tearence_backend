<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SponsoredAdvert extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsored_adverts';

    protected $primaryKey = 'sponsored_advert_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'tagline',
        'description',
        'overview',
        'key_features',
        'what_makes_special',
        'why_sponsored',
        'additional_notes',
        'advert_type',
        'category_id',
        'country',
        'city',
        'latitude',
        'longitude',
        'location_precision',
        'price',
        'currency',
        'condition',
        'main_image',
        'additional_images',
        'video_link',
        'seller_name',
        'business_name',
        'phone',
        'email',
        'website',
        'social_links',
        'logo',
        'verified_seller',
        'sponsorship_tier',
        'sponsorship_price',
        'payment_status',
        'payment_transaction_id',
        'sponsorship_start_date',
        'sponsorship_end_date',
        'views_count',
        'saves_count',
        'inquiries_count',
        'rating',
        'rating_count',
        'is_active',
        'is_featured',
        'sort_order',
        'slug',
        'tags',
        'seo_meta',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sponsorship_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'additional_images' => 'array',
        'social_links' => 'array',
        'tags' => 'array',
        'seo_meta' => 'array',
        'verified_seller' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sponsorship_start_date' => 'datetime',
        'sponsorship_end_date' => 'datetime',
        'views_count' => 'integer',
        'saves_count' => 'integer',
        'inquiries_count' => 'integer',
        'rating' => 'float',
        'rating_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advert) {
            $advert->slug = static::generateUniqueSlug($advert->title);
        });

        static::updating(function ($advert) {
            if ($advert->isDirty('title')) {
                $advert->slug = static::generateUniqueSlug($advert->title);
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the user who created the advert.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the advert.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the category for this advert.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the saves for this advert.
     */
    public function saves()
    {
        return $this->morphMany(Save::class, 'savable');
    }

    /**
     * Get the views for this advert.
     */
    public function views()
    {
        return $this->morphMany(View::class, 'viewable');
    }

    /**
     * Get the inquiries for this advert.
     */
    public function inquiries()
    {
        return $this->hasMany(SponsoredAdvertInquiry::class, 'sponsored_advert_id');
    }

    /**
     * Get the ratings for this advert.
     */
    public function ratings()
    {
        return $this->hasMany(SponsoredAdvertRating::class, 'sponsored_advert_id');
    }

    /**
     * Scope a query to only include active adverts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured adverts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by sponsorship tier.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('sponsorship_tier', $tier);
    }

    /**
     * Scope a query to filter by advert type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('advert_type', $type);
    }

    /**
     * Scope a query to filter by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope a query to only include currently sponsored adverts.
     */
    public function scopeCurrentlySponsored($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('sponsorship_start_date')
              ->orWhere('sponsorship_start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('sponsorship_end_date')
              ->orWhere('sponsorship_end_date', '>=', $now);
        });
    }

    /**
     * Scope a query to order by sponsorship tier (premium first).
     */
    public function scopeOrderByTier($query)
    {
        return $query->orderByRaw("FIELD(sponsorship_tier, 'premium', 'plus', 'basic')");
    }

    /**
     * Scope a query to order by popularity.
     */
    public function scopeOrderByPopularity($query)
    {
        return $query->orderByRaw('(views_count * 0.3 + saves_count * 0.4 + rating * rating_count * 0.3) DESC');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        if (!$this->price) {
            return 'Free';
        }

        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->price, 2);
    }

    /**
     * Get the currency symbol.
     */
    public function getCurrencySymbolAttribute()
    {
        $symbols = [
            'GBP' => '£',
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) {
            return asset('placeholder.png');
        }

        return asset($this->main_image);
    }

    /**
     * Get the additional images URLs.
     */
    public function getAdditionalImagesUrlsAttribute()
    {
        if (!$this->additional_images) {
            return [];
        }

        return collect($this->additional_images)->map(function ($image) {
            return asset($image);
        })->toArray();
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return null;
        }

        return asset($this->logo);
    }

    /**
     * Check if the advert is currently sponsored.
     */
    public function getIsCurrentlySponsoredAttribute()
    {
        $now = Carbon::now();
        
        $startDateValid = !$this->sponsorship_start_date || $this->sponsorship_start_date <= $now;
        $endDateValid = !$this->sponsorship_end_date || $this->sponsorship_end_date >= $now;
        
        return $this->is_active && $startDateValid && $endDateValid;
    }

    /**
     * Increment view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment saves count.
     */
    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    /**
     * Increment inquiries count.
     */
    public function incrementInquiries()
    {
        $this->increment('inquiries_count');
    }

    /**
     * Update rating.
     */
    public function updateRating()
    {
        $ratings = $this->ratings()->where('is_approved', true);
        $count = $ratings->count();
        
        if ($count > 0) {
            $average = $ratings->avg('rating');
            $this->update([
                'rating' => round($average, 2),
                'rating_count' => $count,
            ]);
        } else {
            $this->update([
                'rating' => 0,
                'rating_count' => 0,
            ]);
        }
    }

    /**
     * Get the sponsorship tier display name.
     */
    public function getSponsorshipTierDisplayAttribute()
    {
        $tiers = [
            'basic' => 'Sponsored',
            'plus' => 'Sponsored Plus',
            'premium' => 'Sponsored Premium',
        ];

        return $tiers[$this->sponsorship_tier] ?? ucfirst($this->sponsorship_tier);
    }

    /**
     * Get the country flag emoji.
     */
    public function getCountryFlagAttribute()
    {
        $flags = [
            'GB' => '🇬🇧',
            'US' => '🇺🇸',
            'CA' => '🇨🇦',
            'AU' => '🇦🇺',
            'DE' => '🇩🇪',
            'FR' => '🇫🇷',
            'IT' => '🇮🇹',
            'ES' => '🇪🇸',
            'NL' => '🇳🇱',
            'BE' => '🇧🇪',
            'CH' => '🇨🇭',
            'AT' => '🇦🇹',
            'IE' => '🇮🇪',
            'PT' => '🇵🇹',
            'SE' => '🇸🇪',
            'NO' => '🇳🇴',
            'DK' => '🇩🇰',
            'FI' => '🇫🇮',
            'PL' => '🇵🇱',
            'CZ' => '🇨🇿',
            'GR' => '🇬🇷',
            'TR' => '🇹🇷',
            'IL' => '🇮🇱',
            'AE' => '🇦🇪',
            'SA' => '🇸🇦',
            'IN' => '🇮🇳',
            'PK' => '🇵🇰',
            'BD' => '🇧🇩',
            'LK' => '🇱🇰',
            'NP' => '🇳🇵',
            'TH' => '🇹🇭',
            'MY' => '🇲🇾',
            'SG' => '🇸🇬',
            'ID' => '🇮🇩',
            'PH' => '🇵🇭',
            'VN' => '🇻🇳',
            'JP' => '🇯🇵',
            'KR' => '🇰🇷',
            'CN' => '🇨🇳',
            'HK' => '🇭🇰',
            'TW' => '🇹🇼',
            'MO' => '🇲🇴',
            'NZ' => '🇳🇿',
            'FJ' => '🇫🇯',
            'PG' => '🇵🇬',
            'SB' => '🇸🇧',
            'VU' => '🇻🇺',
            'NC' => '🇳🇨',
            'PF' => '🇵🇫',
            'CK' => '🇨🇰',
            'TO' => '🇹🇴',
            'WS' => '🇼🇸',
            'KI' => '🇰🇮',
            'TV' => '🇹🇻',
            'NU' => '🇳🇺',
            'AS' => '🇦🇸',
            'GU' => '🇬🇺',
            'MP' => '🇲🇵',
            'PW' => '🇵🇼',
            'FM' => '🇫🇲',
            'MH' => '🇲🇭',
            'UM' => '🇺🇲',
            'VI' => '🇻🇮',
            'PR' => '🇵🇷',
            'BQ' => '🇧🇶',
            'CW' => '🇨🇼',
            'SX' => '🇸🇽',
            'AG' => '🇦🇬',
            'AI' => '🇦🇮',
            'AN' => '🇦🇳',
            'AW' => '🇦🇼',
            'BB' => '🇧🇧',
            'BM' => '🇧🇲',
            'BS' => '🇧🇸',
            'BZ' => '🇧🇿',
            'CA' => '🇨🇦',
            'CR' => '🇨🇷',
            'CU' => '🇨🇺',
            'DM' => '🇩🇲',
            'DO' => '🇩🇴',
            'GD' => '🇬🇩',
            'GT' => '🇬🇹',
            'HN' => '🇭🇳',
            'HT' => '🇭🇹',
            'JM' => '🇯🇲',
            'KN' => '🇰🇳',
            'KY' => '🇰🇾',
            'LC' => '🇱🇨',
            'MX' => '🇲🇽',
            'NI' => '🇳🇮',
            'PA' => '🇵🇦',
            'PY' => '🇵🇾',
            'SR' => '🇸🇷',
            'TT' => '🇹🇹',
            'TC' => '🇹🇨',
            'US' => '🇺🇸',
            'UY' => '🇺🇾',
            'VE' => '🇻🇪',
            'VG' => '🇻🇬',
            'AR' => '🇦🇷',
            'BO' => '🇧🇴',
            'BR' => '🇧🇷',
            'CL' => '🇨🇱',
            'CO' => '🇨🇴',
            'EC' => '🇪🇨',
            'FK' => '🇫🇰',
            'GF' => '🇬🇫',
            'GY' => '🇬🇾',
            'PE' => '🇵🇪',
            'PY' => '🇵🇾',
            'SR' => '🇸🇷',
            'UY' => '🇺🇾',
            'VE' => '🇻🇪',
            'DZ' => '🇩🇿',
            'EG' => '🇪🇬',
            'LY' => '🇱🇾',
            'MA' => '🇲🇦',
            'SD' => '🇸🇩',
            'TN' => '🇹🇳',
            'AO' => '🇦🇴',
            'BF' => '🇧🇫',
            'BI' => '🇧🇮',
            'BJ' => '🇧🇯',
            'BW' => '🇧🇼',
            'CD' => '🇨🇩',
            'CF' => '🇨🇫',
            'CG' => '🇨🇬',
            'CI' => '🇨🇮',
            'CM' => '🇨🇲',
            'DJ' => '🇩🇯',
            'ER' => '🇪🇷',
            'ET' => '🇪🇹',
            'GA' => '🇬🇦',
            'GH' => '🇬🇭',
            'GM' => '🇬🇲',
            'GN' => '🇬🇳',
            'GQ' => '🇬🇶',
            'GW' => '🇬🇼',
            'KE' => '🇰🇪',
            'KM' => '🇰🇲',
            'LR' => '🇱🇷',
            'LS' => '🇱🇸',
            'MG' => '🇲🇬',
            'ML' => '🇲🇱',
            'MR' => '🇲🇷',
            'MU' => '🇲🇺',
            'MW' => '🇲🇼',
            'MZ' => '🇲🇿',
            'NA' => '🇳🇦',
            'NE' => '🇳🇪',
            'NG' => '🇳🇬',
            'RW' => '🇷🇼',
            'SC' => '🇸🇨',
            'SL' => '🇸🇱',
            'SN' => '🇸🇳',
            'SO' => '🇸🇴',
            'SS' => '🇸🇸',
            'SZ' => '🇸🇿',
            'TD' => '🇹🇩',
            'TG' => '🇹🇬',
            'TZ' => '🇹🇿',
            'UG' => '🇺🇬',
            'ZA' => '🇿🇦',
            'ZM' => '🇿🇲',
            'ZW' => '🇿🇼',
            'RE' => '🇷🇪',
            'SH' => '🇸🇭',
            'ST' => '🇸🇹',
            'YT' => '🇾🇹',
            'AF' => '🇦🇫',
            'AM' => '🇦🇲',
            'AZ' => '🇦🇿',
            'BH' => '🇧🇭',
            'CN' => '🇨🇳',
            'CY' => '🇨🇾',
            'GE' => '🇬🇪',
            'IR' => '🇮🇷',
            'IQ' => '🇮🇶',
            'JO' => '🇯🇴',
            'KG' => '🇰🇬',
            'KZ' => '🇰🇿',
            'LB' => '🇱🇧',
            'OM' => '🇴🇲',
            'PS' => '🇵🇸',
            'QA' => '🇶🇦',
            'RU' => '🇷🇺',
            'SA' => '🇸🇦',
            'SY' => '🇸🇾',
            'TM' => '🇹🇲',
            'UA' => '🇺🇦',
            'UZ' => '🇺🇿',
            'YE' => '🇾🇪',
        ];

        // Try to get flag by country code first, then by country name
        $countryCode = $this->getCountryCode();
        if ($countryCode && isset($flags[$countryCode])) {
            return $flags[$countryCode];
        }

        return '🌍'; // Default globe icon
    }

    /**
     * Get the ISO country code.
     */
    private function getCountryCode()
    {
        $countryCodes = [
            'United Kingdom' => 'GB',
            'United States' => 'US',
            'USA' => 'US',
            'Canada' => 'CA',
            'Australia' => 'AU',
            'Germany' => 'DE',
            'France' => 'FR',
            'Italy' => 'IT',
            'Spain' => 'ES',
            'Netherlands' => 'NL',
            'Belgium' => 'BE',
            'Switzerland' => 'CH',
            'Austria' => 'AT',
            'Ireland' => 'IE',
            'Portugal' => 'PT',
            'Sweden' => 'SE',
            'Norway' => 'NO',
            'Denmark' => 'DK',
            'Finland' => 'FI',
            'Poland' => 'PL',
            'Czech Republic' => 'CZ',
            'Greece' => 'GR',
            'Turkey' => 'TR',
            'Israel' => 'IL',
            'United Arab Emirates' => 'AE',
            'UAE' => 'AE',
            'Saudi Arabia' => 'SA',
            'India' => 'IN',
            'Pakistan' => 'PK',
            'Bangladesh' => 'BD',
            'Sri Lanka' => 'LK',
            'Nepal' => 'NP',
            'Thailand' => 'TH',
            'Malaysia' => 'MY',
            'Singapore' => 'SG',
            'Indonesia' => 'ID',
            'Philippines' => 'PH',
            'Vietnam' => 'VN',
            'Japan' => 'JP',
            'South Korea' => 'KR',
            'Korea' => 'KR',
            'China' => 'CN',
            'Hong Kong' => 'HK',
            'Taiwan' => 'TW',
            'New Zealand' => 'NZ',
            'Fiji' => 'FJ',
            'Papua New Guinea' => 'PG',
            'Solomon Islands' => 'SB',
            'Vanuatu' => 'VU',
            'New Caledonia' => 'NC',
            'French Polynesia' => 'PF',
            'Cook Islands' => 'CK',
            'Tonga' => 'TO',
            'Samoa' => 'WS',
            'Kiribati' => 'KI',
            'Tuvalu' => 'TV',
            'Niue' => 'NU',
            'American Samoa' => 'AS',
            'Guam' => 'GU',
            'Northern Mariana Islands' => 'MP',
            'Palau' => 'PW',
            'Federated States of Micronesia' => 'FM',
            'Marshall Islands' => 'MH',
            'United States Minor Outlying Islands' => 'UM',
            'Virgin Islands' => 'VI',
            'Puerto Rico' => 'PR',
            'Bonaire' => 'BQ',
            'Curacao' => 'CW',
            'Sint Maarten' => 'SX',
            'Antigua and Barbuda' => 'AG',
            'Anguilla' => 'AI',
            'Netherlands Antilles' => 'AN',
            'Aruba' => 'AW',
            'Barbados' => 'BB',
            'Bermuda' => 'BM',
            'Bahamas' => 'BS',
            'Belize' => 'BZ',
            'Costa Rica' => 'CR',
            'Cuba' => 'CU',
            'Dominica' => 'DM',
            'Dominican Republic' => 'DO',
            'Grenada' => 'GD',
            'Guatemala' => 'GT',
            'Honduras' => 'HN',
            'Haiti' => 'HT',
            'Jamaica' => 'JM',
            'Saint Kitts and Nevis' => 'KN',
            'Cayman Islands' => 'KY',
            'Saint Lucia' => 'LC',
            'Mexico' => 'MX',
            'Nicaragua' => 'NI',
            'Panama' => 'PA',
            'Paraguay' => 'PY',
            'Suriname' => 'SR',
            'Trinidad and Tobago' => 'TT',
            'Turks and Caicos Islands' => 'TC',
            'British Virgin Islands' => 'VG',
            'Argentina' => 'AR',
            'Bolivia' => 'BO',
            'Brazil' => 'BR',
            'Chile' => 'CL',
            'Colombia' => 'CO',
            'Ecuador' => 'EC',
            'Falkland Islands' => 'FK',
            'French Guiana' => 'GF',
            'Guyana' => 'GY',
            'Peru' => 'PE',
            'Uruguay' => 'UY',
            'Venezuela' => 'VE',
            'Algeria' => 'DZ',
            'Egypt' => 'EG',
            'Libya' => 'LY',
            'Morocco' => 'MA',
            'Sudan' => 'SD',
            'Tunisia' => 'TN',
            'Angola' => 'AO',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Benin' => 'BJ',
            'Botswana' => 'BW',
            'Democratic Republic of the Congo' => 'CD',
            'Central African Republic' => 'CF',
            'Republic of the Congo' => 'CG',
            'Ivory Coast' => 'CI',
            'Cameroon' => 'CM',
            'Djibouti' => 'DJ',
            'Eritrea' => 'ER',
            'Ethiopia' => 'ET',
            'Gabon' => 'GA',
            'Ghana' => 'GH',
            'Gambia' => 'GM',
            'Guinea' => 'GN',
            'Equatorial Guinea' => 'GQ',
            'Guinea-Bissau' => 'GW',
            'Kenya' => 'KE',
            'Comoros' => 'KM',
            'Liberia' => 'LR',
            'Lesotho' => 'LS',
            'Madagascar' => 'MG',
            'Mali' => 'ML',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Malawi' => 'MW',
            'Mozambique' => 'MZ',
            'Namibia' => 'NA',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'Rwanda' => 'RW',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Senegal' => 'SN',
            'Somalia' => 'SO',
            'South Sudan' => 'SS',
            'Eswatini' => 'SZ',
            'Chad' => 'TD',
            'Togo' => 'TG',
            'Tanzania' => 'TZ',
            'Uganda' => 'UG',
            'South Africa' => 'ZA',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
            'Reunion' => 'RE',
            'Saint Helena' => 'SH',
            'Sao Tome and Principe' => 'ST',
            'Mayotte' => 'YT',
            'Afghanistan' => 'AF',
            'Armenia' => 'AM',
            'Azerbaijan' => 'AZ',
            'Bahrain' => 'BH',
            'Cyprus' => 'CY',
            'Georgia' => 'GE',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Jordan' => 'JO',
            'Kyrgyzstan' => 'KG',
            'Kazakhstan' => 'KZ',
            'Lebanon' => 'LB',
            'Oman' => 'OM',
            'Palestine' => 'PS',
            'Qatar' => 'QA',
            'Russia' => 'RU',
            'Syria' => 'SY',
            'Turkmenistan' => 'TM',
            'Uzbekistan' => 'UZ',
            'Yemen' => 'YE',
        ];

        return $countryCodes[$this->country] ?? null;
    }
}
