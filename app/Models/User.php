<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasName;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements FilamentUser, HasName
{
    // use HasApiTokens, HasFactory, Notifiable;
    protected $appends = ['name'];

    public function canAccessPanel(Panel $panel): bool
    {
        // Temporarily allow all panel access for debugging
        return true;
        
        // Original logic:
        // return $this->hasVerifiedEmail() && $this->isKycVerified();
    }

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_uid',
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'group_id',
        'permissions',
        'is_super_admin',
        'can_manage_users',
        'can_manage_categories',
        'can_manage_listings',
        'can_manage_dashboard',
        'can_view_analytics',
        'is_active',
        'timezone',
        'avatar',
        'kyc_status',
        'kyc_documents',
        'kyc_verified_at',
        'kyc_rejection_reason',
        'posts_count',
        'posts_limit',
        'last_post_at',
        'email_verified',
        'mobile_verified',
        'mobile_verified_at',
        'mobile_number',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'password_reset_key',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'last_post_at' => 'datetime',
        'permissions' => 'array',
        'kyc_documents' => 'array',
        'is_super_admin' => 'boolean',
        'can_manage_users' => 'boolean',
        'can_manage_categories' => 'boolean',
        'can_manage_listings' => 'boolean',
        'can_manage_dashboard' => 'boolean',
        'can_view_analytics' => 'boolean',
        'is_active' => 'boolean',
        'email_verified' => 'boolean',
        'mobile_verified' => 'boolean',
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

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->is_super_admin) {
            return true;
        }

        // Check user-specific permissions
        if (is_array($this->permissions) && in_array($permission, $this->permissions)) {
            return true;
        }

        // Check group permissions
        if ($this->group) {
            if (is_array($this->group->permissions) && in_array($permission, $this->group->permissions)) {
                return true;
            }
        }

        // Check specific permission flags
        return match($permission) {
            'manage_users' => $this->can_manage_users,
            'manage_categories' => $this->can_manage_categories,
            'manage_listings' => $this->can_manage_listings,
            'manage_dashboard' => $this->can_manage_dashboard,
            'view_analytics' => $this->can_view_analytics,
            default => false,
        };
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users');
    }

    /**
     * Check if user can manage categories
     */
    public function canManageCategories(): bool
    {
        return $this->hasPermission('manage_categories');
    }

    /**
     * Check if user can manage listings
     */
    public function canManageListings(): bool
    {
        return $this->hasPermission('manage_listings');
    }

    /**
     * Check if user has completed KYC verification
     */
    public function isKycVerified(): bool
    {
        // KYC is deactivated for now, return true
        return true;
        
        // Original logic (commented out):
        // return $this->kyc_status === 'verified' && $this->kyc_verified_at !== null;
    }

    /**
     * Check if user can access website features (no longer requires KYC)
     */
    public function canAccessWebsite(): bool
    {
        // Only require email verification for basic access
        return $this->email_verified;
    }

    /**
     * Check if user can post listings
     */
    public function canPostListing(): bool
    {
        // Check if user has reached posting limit
        if ($this->posts_count >= $this->posts_limit) {
            // Allow unlimited posting if KYC is verified
            if ($this->kyc_status === 'verified' && $this->kyc_verified_at !== null) {
                return true;
            }
            return false;
        }
        
        return true;
    }

    /**
     * Increment user's post count
     */
    public function incrementPostCount(): void
    {
        $this->increment('posts_count');
        $this->update(['last_post_at' => now()]);
    }

    /**
     * Check if user needs KYC verification (after reaching posting limit)
     */
    public function needsKycVerification(): bool
    {
        return $this->posts_count >= $this->posts_limit && $this->kyc_status === 'disabled';
    }

    /**
     * Enable KYC requirement for user
     */
    public function enableKycRequirement(): void
    {
        $this->update(['kyc_status' => 'pending']);
    }

    /**
     * Check if user's email is verified
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified && $this->email_verified_at !== null;
    }

    /**
     * Check if user's mobile is verified
     */
    public function isMobileVerified(): bool
    {
        return $this->mobile_verified && $this->mobile_verified_at !== null;
    }

    /**
     * Verify user's email
     */
    public function verifyEmail(): void
    {
        $this->update([
            'email_verified' => true,
            'email_verified_at' => now()
        ]);
    }

    /**
     * Verify user's mobile
     */
    public function verifyMobile(): void
    {
        $this->update([
            'mobile_verified' => true,
            'mobile_verified_at' => now()
        ]);
    }

    /**
     * Submit KYC documents for verification
     */
    public function submitKyc(array $documents): void
    {
        $this->kyc_documents = $documents;
        $this->kyc_status = 'submitted';
        $this->kyc_rejection_reason = null;
        $this->save();
    }

    /**
     * Approve KYC verification
     */
    public function approveKyc(): void
    {
        $this->kyc_status = 'verified';
        $this->kyc_verified_at = now();
        $this->kyc_rejection_reason = null;
        $this->save();
    }

    /**
     * Reject KYC verification
     */
    public function rejectKyc(string $reason): void
    {
        $this->kyc_status = 'rejected';
        $this->kyc_rejection_reason = $reason;
        $this->save();
    }
}
