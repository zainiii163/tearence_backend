<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view all users
        return $user->is_super_admin || $user->can_manage_users;
    }

    /**
     * Determine whether user can view a specific user.
     */
    public function view(User $user, User $targetUser): bool
    {
        // Admin can view all, users can view their own profile
        return $user->is_super_admin || 
               $user->can_manage_users || 
               $user->user_id === $targetUser->user_id;
    }

    /**
     * Determine whether user can create users.
     */
    public function create(User $user): bool
    {
        // Only admins can create users
        return $user->is_super_admin || $user->can_manage_users;
    }

    /**
     * Determine whether user can update a user.
     */
    public function update(User $user, User $targetUser): bool
    {
        // Admin can update all, users can update their own profile
        return $user->is_super_admin || 
               $user->can_manage_users || 
               $user->user_id === $targetUser->user_id;
    }

    /**
     * Determine whether user can delete a user.
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Only super admin can delete users, and cannot delete themselves
        return $user->is_super_admin && $user->user_id !== $targetUser->user_id;
    }

    /**
     * Determine whether user can manage user permissions.
     */
    public function managePermissions(User $user): bool
    {
        // Only super admin can manage permissions
        return $user->is_super_admin;
    }

    /**
     * Determine whether user can view KYC information.
     */
    public function viewKyc(User $user, User $targetUser): bool
    {
        // Admin can view all KYC, users can view their own KYC
        return $user->is_super_admin || 
               $user->can_manage_users || 
               $user->user_id === $targetUser->user_id;
    }

    /**
     * Determine whether user can approve KYC.
     */
    public function approveKyc(User $user): bool
    {
        // Admin can approve KYC
        return $user->is_super_admin || $user->can_manage_users;
    }

    /**
     * Determine whether user can reject KYC.
     */
    public function rejectKyc(User $user): bool
    {
        // Admin can reject KYC
        return $user->is_super_admin || $user->can_manage_users;
    }

    /**
     * Determine whether user can view analytics.
     */
    public function viewAnalytics(User $user, User $targetUser): bool
    {
        // Admin can view all analytics, users can view their own
        return $user->is_super_admin || 
               $user->can_view_analytics || 
               $user->user_id === $targetUser->user_id;
    }

    /**
     * Determine whether user can manage dashboard.
     */
    public function manageDashboard(User $user): bool
    {
        // Admin can manage dashboard
        return $user->is_super_admin || $user->can_manage_dashboard;
    }
}
