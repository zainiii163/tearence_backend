<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffManagement extends Model
{
    use HasFactory;

    protected $primaryKey = 'staff_id';
    protected $table = 'staff_management';

    protected $fillable = [
        'customer_id',
        'staff_customer_id',
        'entity_type',
        'entity_id',
        'permissions',
        'role',
        'can_post_ads',
        'can_edit_ads',
        'can_delete_ads',
        'can_manage_payments',
        'can_view_analytics',
        'can_manage_staff',
        'is_active',
        'invited_at',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'can_post_ads' => 'boolean',
        'can_edit_ads' => 'boolean',
        'can_delete_ads' => 'boolean',
        'can_manage_payments' => 'boolean',
        'can_view_analytics' => 'boolean',
        'can_manage_staff' => 'boolean',
        'is_active' => 'boolean',
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the owner customer
     */
    public function owner()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the staff member customer
     */
    public function staffMember()
    {
        return $this->belongsTo(Customer::class, 'staff_customer_id', 'customer_id');
    }

    /**
     * Get the business entity (if applicable)
     */
    public function business()
    {
        if ($this->entity_type === 'business') {
            return $this->belongsTo(CustomerBusiness::class, 'entity_id', 'id');
        }
        return null;
    }

    /**
     * Get the store entity (if applicable)
     */
    public function store()
    {
        if ($this->entity_type === 'store') {
            return $this->belongsTo(CustomerStore::class, 'entity_id', 'store_id');
        }
        return null;
    }

    /**
     * Check if staff member has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check specific permission flags
        return match($permission) {
            'post_ads' => $this->can_post_ads,
            'edit_ads' => $this->can_edit_ads,
            'delete_ads' => $this->can_delete_ads,
            'manage_payments' => $this->can_manage_payments,
            'view_analytics' => $this->can_view_analytics,
            'manage_staff' => $this->can_manage_staff,
            default => false,
        };
    }
}
