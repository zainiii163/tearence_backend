<?php

namespace App\Policies;

use App\Models\PromotedAdvert;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotedAdvertPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any promoted adverts.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all, users can view their own
        return $user->isAdmin() || true;
    }

    /**
     * Determine whether the user can view the promoted advert.
     */
    public function view(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Admin can view all, users can view their own
        return $user->isAdmin() || $user->id === $promotedAdvert->user_id;
    }

    /**
     * Determine whether the user can create promoted adverts.
     */
    public function create(User $user): bool
    {
        // Admin and authenticated users can create
        return $user->isAdmin() || $user->isAuthenticated();
    }

    /**
     * Determine whether the user can update the promoted advert.
     */
    public function update(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Admin can update all, users can update their own
        return $user->isAdmin() || $user->id === $promotedAdvert->user_id;
    }

    /**
     * Determine whether the user can delete the promoted advert.
     */
    public function delete(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Admin can delete all, users can delete their own
        return $user->isAdmin() || $user->id === $promotedAdvert->user_id;
    }

    /**
     * Determine whether the user can restore the promoted advert.
     */
    public function restore(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Admin can restore all, users can restore their own
        return $user->isAdmin() || $user->id === $promotedAdvert->user_id;
    }

    /**
     * Determine whether the user can permanently delete the promoted advert.
     */
    public function forceDelete(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Only admin can force delete
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can approve promoted adverts.
     */
    public function approve(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Only admin can approve
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject promoted adverts.
     */
    public function reject(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Only admin can reject
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can feature promoted adverts.
     */
    public function feature(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Only admin can feature
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view analytics.
     */
    public function viewAnalytics(User $user, PromotedAdvert $promotedAdvert): bool
    {
        // Admin can view all analytics, users can view their own
        return $user->isAdmin() || $user->id === $promotedAdvert->user_id;
    }

    /**
     * Determine whether the user can export promoted adverts.
     */
    public function export(User $user): bool
    {
        // Only admin can export
        return $user->isAdmin();
    }
}
