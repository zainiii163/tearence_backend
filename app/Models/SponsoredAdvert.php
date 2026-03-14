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

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'currency',
        'category_id',
        'country',
        'city',
        'images',
        'video_url',
        'seller_info',
        'location',
        'views',
        'rating',
        'reviews_count',
        'featured',
        'promoted',
        'sponsored',
        'status',
        'promotion_plan',
        'promotion_expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'images' => 'array',
        'seller_info' => 'array',
        'location' => 'array',
        'views' => 'integer',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'featured' => 'boolean',
        'promoted' => 'boolean',
        'sponsored' => 'boolean',
        'promotion_expires_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get the user who created the advert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category for this advert.
     */
    public function category()
    {
        return $this->belongsTo(SponsoredCategory::class);
    }

    /**
     * Get the analytics for this advert.
     */
    public function analytics()
    {
        return $this->hasMany(SponsoredAnalytic::class, 'advert_id');
    }

    /**
     * Get the saves for this advert.
     */
    public function saves()
    {
        return $this->hasMany(SavedAdvert::class, 'advert_id');
    }

    /**
     * Scope a query to only include active adverts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
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
     * Scope a query to only include sponsored adverts.
     */
    public function scopeSponsored($query)
    {
        return $query->where('sponsored', true);
    }

    /**
     * Scope a query to only include featured adverts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include promoted adverts.
     */
    public function scopePromoted($query)
    {
        return $query->where('promoted', true);
    }

    /**
     * Scope a query to order by popularity.
     */
    public function scopeOrderByPopularity($query)
    {
        return $query->orderBy('views', 'desc');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        if (!$this->price) {
            return 'Free';
        }

        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the first image URL.
     */
    public function getFirstImageUrlAttribute()
    {
        if (!$this->images || empty($this->images)) {
            return asset('placeholder.png');
        }

        return $this->images[0];
    }

    /**
     * Check if the advert is currently promoted.
     */
    public function getIsCurrentlyPromotedAttribute()
    {
        return $this->promotion_expires_at && $this->promotion_expires_at > now();
    }

    /**
     * Increment view count.
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Track analytics event.
     */
    public function trackEvent($eventType, $metadata = [], $userId = null)
    {
        return $this->analytics()->create([
            'event_type' => $eventType,
            'metadata' => $metadata,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
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
