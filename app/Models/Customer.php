<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory, HasApiTokens;
    
    protected $appends = ['name'];

    protected $guarded = [];
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'customer_id';

    /**
     * Guard for the model
     *
     * @var string
     */
    protected $guard = 'api';

     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAvatarAttribute($value)
    {
        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($value, 'avatar');
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['currency', 'location'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'currency_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'customer_id', 'customer_id');
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the candidate profile for the customer.
     */
    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the referral created by this customer
     */
    public function referral()
    {
        return $this->hasOne(Referral::class, 'referrer_id', 'customer_id');
    }

    /**
     * Get user referrals where this customer is the referrer
     */
    public function sentReferrals()
    {
        return $this->hasMany(UserReferral::class, 'referrer_user_id', 'customer_id');
    }

    /**
     * Get user referrals where this customer was referred
     */
    public function receivedReferral()
    {
        return $this->hasOne(UserReferral::class, 'referred_user_id', 'customer_id');
    }

    /**
     * Get all referral activities for this customer
     */
    public function referralActivities()
    {
        return $this->hasMany(UserReferral::class, 'referrer_user_id', 'customer_id')
                   ->orWhere('referred_user_id', $this->customer_id);
    }

    /**
     * Find user by email or phone
     */
    public static function findByEmailOrPhone($email = null, $phone = null)
    {
        if (!$email && !$phone) {
            return null;
        }

        $query = static::query();

        if ($email) {
            $query->orWhere('email', $email);
        }

        if ($phone) {
            $query->orWhere('phone_number', $phone);
        }

        return $query->first();
    }

    /**
     * Search users by email or phone with partial matching
     */
    public static function searchByEmailOrPhone($search)
    {
        if (!$search) {
            return collect();
        }

        return static::where(function($query) use ($search) {
            $query->where('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone_number', 'LIKE', "%{$search}%");
        })
        ->select('customer_id', 'first_name', 'last_name', 'email', 'phone_number')
        ->limit(10)
        ->get();
    }
}
