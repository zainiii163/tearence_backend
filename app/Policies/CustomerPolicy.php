<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorizes Filament/admin actions on Customer records.
 * (Previously this file was a mistaken copy of VehiclePolicy.)
 */
class CustomerPolicy
{
    use HandlesAuthorization;

    protected function canManageCustomers(User $user): bool
    {
        return (bool) (
            ($user->is_super_admin ?? false)
            || ($user->can_manage_users ?? false)
            || ($user->can_manage_dashboard ?? false)
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
        );
    }

    public function viewAny(User $user): bool
    {
        return $this->canManageCustomers($user);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->canManageCustomers($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCustomers($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->canManageCustomers($user);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return (bool) ($user->is_super_admin ?? false);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return (bool) ($user->is_super_admin ?? false);
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return (bool) ($user->is_super_admin ?? false);
    }
}
