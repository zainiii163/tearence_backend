<?php

namespace App\Policies;

use App\Models\Vehicle;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether user can view any vehicles.
     */
    public function viewAny($user): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $isAuthenticated = $this->isAuthenticated($user);
        
        // Admin can view all, authenticated users can view their own
        return $isAdmin || $isAuthenticated;
    }

    /**
     * Determine whether user can view a specific vehicle.
     */
    public function view($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can view all, users can view their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Determine whether user can create vehicles.
     */
    public function create($user): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $isAuthenticated = $this->isAuthenticated($user);
        
        // Admin and authenticated users can create
        return $isAdmin || $isAuthenticated;
    }

    /**
     * Determine whether user can update a vehicle.
     */
    public function update($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can update all, users can update their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Determine whether user can delete a vehicle.
     */
    public function delete($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can delete all, users can delete their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Determine whether user can favourite a vehicle.
     */
    public function favourite($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can favourite all, users can favourite their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Determine whether user can view analytics for a vehicle.
     */
    public function viewAnalytics($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can view all analytics, users can view their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Determine whether user can submit enquiries for a vehicle.
     */
    public function submitEnquiry($user): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $isAuthenticated = $this->isAuthenticated($user);
        
        // Admin and authenticated users can submit enquiries
        return $isAdmin || $isAuthenticated;
    }

    /**
     * Determine whether user can view enquiries for a vehicle.
     */
    public function viewEnquiries($user, Vehicle $vehicle): bool
    {
        // Handle both User and Customer models
        $isAdmin = $this->isAdmin($user);
        $userId = $this->getUserId($user);
        
        // Admin can view all enquiries, users can view their own
        return $isAdmin || $userId === $vehicle->user_id;
    }

    /**
     * Helper method to check if user is admin for both User and Customer models
     */
    private function isAdmin($user): bool
    {
        if ($user instanceof User) {
            return $user->isAdmin();
        } elseif ($user instanceof Customer) {
            return $user->isAdmin();
        }
        return false;
    }

    /**
     * Helper method to check if user is authenticated for both User and Customer models
     */
    private function isAuthenticated($user): bool
    {
        if ($user instanceof User) {
            return $user->isAuthenticated();
        } elseif ($user instanceof Customer) {
            return $user->isAuthenticated();
        }
        return false;
    }

    /**
     * Helper method to get user ID for both User and Customer models
     */
    private function getUserId($user)
    {
        if ($user instanceof User) {
            return $user->id;
        } elseif ($user instanceof Customer) {
            return $user->customer_id;
        }
        return null;
    }
}
