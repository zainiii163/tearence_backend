<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Ensures the owner/team super-admin logins work on BOTH sides of WWA:
 *
 *  - users table   -> Filament admin panel (https://api.worldwideadverts.info/admin)
 *  - customer table -> WWA web app Super Admin Dashboard (/admin/dashboard)
 *    (web-login authenticates customers, and Customer::isAdmin() grants
 *     admin API access via role/is_super_admin/customer_id #1)
 *
 * Run on server: php artisan db:seed --class=OwnerSuperAdminSeeder --force
 */
class OwnerSuperAdminSeeder extends Seeder
{
    /**
     * @var array<int, array{email: string, first: string, last: string, password: string, reset_customer_password: bool}>
     */
    protected array $accounts = [
        [
            'email' => 'hanzoali96@gmail.com',
            'first' => 'Hanzo',
            'last' => 'Admin',
            'password' => 'password123',
            // No usable customer login existed for this email before.
            'reset_customer_password' => true,
        ],
        [
            'email' => 'worldwideadverts@gmail.com',
            'first' => 'Clive',
            'last' => 'Admin',
            'password' => 'CliveAdmin2026',
            // Customer login was reported inaccessible; restore it.
            'reset_customer_password' => true,
        ],
        [
            'email' => 'rizky@worldwideadverts.info',
            'first' => 'Rizky',
            'last' => 'Admin',
            'password' => 'admin123',
            // Rizky actively uses this login - never rotate his password here,
            // just make sure the super-admin flags are present.
            'reset_customer_password' => false,
        ],
    ];

    public function run(): void
    {
        foreach ($this->accounts as $account) {
            $this->ensureFilamentUser($account);
            $this->ensureCustomer($account);
        }

        $this->command?->info('WWA Web Super Admin: https://worldwideadverts.info/admin/dashboard');
        $this->command?->info('Filament Admin Panel: https://api.worldwideadverts.info/admin');
    }

    protected function ensureFilamentUser(array $account): void
    {
        $user = User::where('email', $account['email'])->first();

        if (! $user) {
            User::create([
                'user_uid' => Str::random(13),
                'first_name' => $account['first'],
                'last_name' => $account['last'],
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'is_super_admin' => true,
                'can_manage_users' => true,
                'can_manage_categories' => true,
                'can_manage_listings' => true,
                'can_manage_dashboard' => true,
                'can_view_analytics' => true,
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
            ]);
            $this->command?->info("Created Filament admin: {$account['email']}");

            return;
        }

        $user->is_super_admin = true;
        $user->can_manage_users = true;
        $user->can_manage_categories = true;
        $user->can_manage_listings = true;
        $user->can_manage_dashboard = true;
        $user->can_view_analytics = true;
        $user->email_verified_at = $user->email_verified_at ?? now();
        if ($user->isDirty()) {
            $user->save();
            $this->command?->info("Updated Filament admin flags: {$account['email']}");
        }
    }

    protected function ensureCustomer(array $account): void
    {
        $hasRoleColumns = \Schema::hasColumn('customer', 'role');

        $customer = Customer::where('email', $account['email'])->first();

        if (! $customer) {
            $customer = new Customer([
                'customer_uid' => substr(strtoupper(Str::random(8)), 0, 8),
                'first_name' => $account['first'],
                'last_name' => $account['last'],
                'email' => $account['email'],
                'password_hash' => Hash::make($account['password']),
                'email_verified_at' => now(),
            ]);
            $customer->save();
            $this->command?->info("Created customer super admin: {$account['email']}");
        } elseif ($account['reset_customer_password']) {
            $customer->password_hash = Hash::make($account['password']);
            $customer->email_verified_at = $customer->email_verified_at ?? now();
            $customer->save();
            $this->command?->info("Restored customer super admin password: {$account['email']}");
        }

        // Grant/refresh admin flags without touching passwords again.
        $dirty = false;
        if ($hasRoleColumns) {
            if ($customer->role !== 'super_admin') {
                $customer->role = 'super_admin';
                $dirty = true;
            }
            if (empty($customer->is_super_admin)) {
                $customer->is_super_admin = true;
                $dirty = true;
            }
        }
        if (empty($customer->email_verified_at)) {
            $customer->email_verified_at = now();
            $dirty = true;
        }
        if ($dirty) {
            $customer->save();
            $this->command?->info("Refreshed customer admin flags: {$account['email']}");
        }
    }
}
