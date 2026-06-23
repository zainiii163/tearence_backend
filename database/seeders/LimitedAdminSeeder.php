<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LimitedAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Shihab's admin account
        $shihab = User::updateOrCreate(
            ['email' => 'shihab@worldwideadverts.info'],
            [
                'user_uid' => 'SHIHAB' . rand(1000, 9999),
                'first_name' => 'Shihab',
                'last_name' => 'Admin',
                'password' => Hash::make('Admin@123'), // Default password, should be changed on first login
                'group_id' => 1,
                'is_super_admin' => false,
                'can_manage_users' => false, // Cannot manage users
                'can_manage_categories' => true, // Can manage categories
                'can_manage_listings' => true, // Can manage listings (post/edit content)
                'can_manage_dashboard' => false, // Cannot access dashboard (financial data)
                'can_view_analytics' => false, // Cannot view analytics
                'email_verified' => true,
                'email_verified_at' => now(),
                'posts_count' => 0,
                'posts_limit' => 999, // Unlimited posting
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
            ]
        );

        // Create Vikas's admin account
        $vikas = User::updateOrCreate(
            ['email' => 'vikas@worldwideadverts.info'],
            [
                'user_uid' => 'VIKAS' . rand(1000, 9999),
                'first_name' => 'Vikas',
                'last_name' => 'Admin',
                'password' => Hash::make('Admin@123'), // Default password, should be changed on first login
                'group_id' => 1,
                'is_super_admin' => false,
                'can_manage_users' => false, // Cannot manage users
                'can_manage_categories' => true, // Can manage categories
                'can_manage_listings' => true, // Can manage listings (post/edit content)
                'can_manage_dashboard' => false, // Cannot access dashboard (financial data)
                'can_view_analytics' => false, // Cannot view analytics
                'email_verified' => true,
                'email_verified_at' => now(),
                'posts_count' => 0,
                'posts_limit' => 999, // Unlimited posting
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
            ]
        );

        $this->command->info('Limited admin accounts created:');
        $this->command->info('- Shihab: shihab@worldwideadverts.info / Admin@123');
        $this->command->info('- Vikas: vikas@worldwideadverts.info / Admin@123');
        $this->command->info('');
        $this->command->info('Permissions: Can manage categories and listings only');
        $this->command->info('Restricted: Cannot manage users, access dashboard, or view analytics');
        $this->command->info('');
        $this->command->info('IMPORTANT: Users should change their passwords on first login!');
    }
}
