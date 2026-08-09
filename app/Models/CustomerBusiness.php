<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBusiness extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_business';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'slug',
        'business_name',
        'business_description',
        'business_phone_number',
        'business_address',
        'city',
        'country',
        'business_email',
        'business_logo',
        'cover_image',
        'business_website',
        'booking_url',
        'business_owner',
        'status',
        'business_company_no',
        'business_company_name',
        'business_company_registration',
        'personal_email',
        'personal_phone_number',
        'category_id',
        'vat_number',
        'business_category_slug',
        'category_profile',
    ];

    protected $casts = [
        'category_profile' => 'array',
    ];

    protected $appends = [
        'profile',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * Frontend-friendly alias for category_profile JSON.
     */
    public function getProfileAttribute()
    {
        $profile = $this->category_profile;
        return is_array($profile) ? $profile : [];
    }

    public function getBusinessLogoAttribute($value)
    {
        if (!$value) {
            return null;
        }
        if (is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:'))) {
            return $value;
        }
        $fileUpload = new FileUploadHelper();
        $path = str_replace("/uploads/images/business", "", $value);
        return $fileUpload->getFile($path, 'business');
    }
}
