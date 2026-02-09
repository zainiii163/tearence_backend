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
        'business_phone_number',
        'business_address',
        'business_email',
        'business_logo',
        'business_website',
        'business_owner',
        'status',
        'business_company_no',
        'business_company_name',
        'business_company_registration',
        'personal_email',
        'personal_phone_number',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function getBusinessLogoAttribute($value)
    {
        if (!$value) {
            return null;
        }
        $fileUpload = new FileUploadHelper();
        $path = str_replace("/uploads/images/business", "", $value);
        return $fileUpload->getFile($path, 'business');
    }
}
