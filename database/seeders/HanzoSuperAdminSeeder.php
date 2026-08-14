<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Super admin for hanzoali96@gmail.com
 * Run on server: php artisan db:seed --class=HanzoSuperAdminSeeder --force
 */
class HanzoSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'hanzoali96@gmail.com';
        $password = 'password123';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'user_uid' => Str::random(13),
                'first_name' => 'Hanzo',
                'last_name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
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
            $this->command?->info("Created super admin: {$email}");
        } else {
            $user->password = Hash::make($password);
            $user->is_super_admin = true;
            $user->can_manage_users = true;
            $user->can_manage_categories = true;
            $user->can_manage_listings = true;
            $user->can_manage_dashboard = true;
            $user->can_view_analytics = true;
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->save();
            $this->command?->info("Updated super admin: {$email}");
        }

        $this->command?->info('Login: https://api.worldwideadverts.info/admin');
        $this->command?->info("Email: {$email}");
        $this->command?->info("Password: {$password}");
    }
}
