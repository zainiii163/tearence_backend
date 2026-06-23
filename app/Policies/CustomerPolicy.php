<?php

namespace App\Policies;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether customer can view any vehicles.
     */
    public function viewAny(Customer|User $customer): bool
    {
        // All authenticated customers/users can view vehicles
        return $customer->isAuthenticated();
    }

    /**
     * Determine whether customer can view a specific vehicle.
     */
    public function view(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can view all, customers can view their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }

    /**
     * Determine whether customer can create vehicles.
     */
    public function create(Customer|User $customer): bool
    {
        // Admin and authenticated customers can create
        return $customer->isAdmin() || $customer->isAuthenticated();
    }

    /**
     * Determine whether customer can update a vehicle.
     */
    public function update(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can update all, customers can update their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }

    /**
     * Determine whether customer can delete a vehicle.
     */
    public function delete(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can delete all, customers can delete their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }

    /**
     * Determine whether customer can favourite a vehicle.
     */
    public function favourite(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can favourite all, customers can favourite their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }

    /**
     * Determine whether customer can view analytics for a vehicle.
     */
    public function viewAnalytics(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can view all analytics, customers can view their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }

    /**
     * Determine whether customer can submit enquiries for a vehicle.
     */
    public function submitEnquiry(Customer|User $customer): bool
    {
        // Admin and authenticated customers can submit enquiries
        return $customer->isAdmin() || $customer->isAuthenticated();
    }

    /**
     * Determine whether customer can view enquiries for a vehicle.
     */
    public function viewEnquiries(Customer|User $customer, Vehicle $vehicle): bool
    {
        // Admin can view all enquiries, customers can view their own
        if ($customer instanceof User) {
            return $customer->isAdmin() || $customer->user_id === $vehicle->user_id;
        }
        return $customer->isAdmin() || $customer->customer_id === $vehicle->user_id;
    }
}
