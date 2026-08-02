<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Restores Clive's admin login without removing Rizky super admin.
 * Run on server: php artisan db:seed --class=RestoreCliveAdminSeeder --force
 */
class RestoreCliveAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'worldwideadverts@gmail.com';
        $tempPassword = 'Password.1';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'user_uid' => Str::random(13),
                'first_name' => 'Clive',
                'last_name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($tempPassword),
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
            $this->command?->info("Created Clive admin: {$email}");
        } else {
            $user->password = Hash::make($tempPassword);
            $user->is_super_admin = true;
            $user->can_manage_users = true;
            $user->can_manage_categories = true;
            $user->can_manage_listings = true;
            $user->can_manage_dashboard = true;
            $user->can_view_analytics = true;
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            $this->command?->info("Updated Clive admin password + super admin flags: {$email}");
        }

        $this->command?->warn('Temporary password set. Ask Clive to change it after login.');
        $this->command?->info('Login URL: https://api.worldwideadverts.info/admin');
        $this->command?->info("Email: {$email}");
    }
}
