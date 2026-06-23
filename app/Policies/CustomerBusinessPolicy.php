<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomerBusiness;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerBusinessPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomerBusiness $customerBusiness): bool
    {
        // Super admins can view all businesses
        if ($user->is_super_admin) {
            return true;
        }

        // Users with manage_dashboard permission can view all businesses
        if ($user->can_manage_dashboard) {
            return true;
        }

        // Users can view their own businesses
        return $customerBusiness->customer_id === $user->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create businesses
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomerBusiness $customerBusiness): bool
    {
        // Super admins can update all businesses
        if ($user->is_super_admin) {
            return true;
        }

        // Users with manage_dashboard permission can update all businesses
        if ($user->can_manage_dashboard) {
            return true;
        }

        // Users can update their own businesses
        return $customerBusiness->customer_id === $user->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerBusiness $customerBusiness): bool
    {
        // Super admins can delete all businesses
        if ($user->is_super_admin) {
            return true;
        }

        // Users with manage_dashboard permission can delete all businesses
        if ($user->can_manage_dashboard) {
            return true;
        }

        // Users can delete their own businesses
        return $customerBusiness->customer_id === $user->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomerBusiness $customerBusiness): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomerBusiness $customerBusiness): bool
    {
        return false;
    }
}
