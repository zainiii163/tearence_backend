<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user with rizky@worldwideadverts.info
        $admin = User::where('email', 'rizky@worldwideadverts.info')->first();

        if (!$admin) {
            $admin = User::create([
                'user_uid' => Str::random(13),
                'first_name' => 'Rizky',
                'last_name' => 'Admin',
                'email' => 'rizky@worldwideadverts.info',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'group_id' => null,
                'timezone' => 'UTC',
                'is_super_admin' => true,
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_documents' => json_encode([
                    [
                        'type' => 'passport',
                        'path' => 'kyc_documents/admin_passport.jpg',
                        'original_name' => 'admin_passport.jpg',
                        'uploaded_at' => now()->toISOString()
                    ]
                ]),
            ]);

            $this->command->info('Super admin user created successfully!');
            $this->command->info('Email: rizky@worldwideadverts.info');
            $this->command->info('Password: admin123');
        } else {
            // Update existing admin with KYC and super admin status
            $admin->is_super_admin = true;
            $admin->kyc_status = 'verified';
            $admin->kyc_verified_at = now();
            $admin->kyc_documents = json_encode([
                [
                    'type' => 'passport',
                    'path' => 'kyc_documents/admin_passport.jpg',
                    'original_name' => 'admin_passport.jpg',
                    'uploaded_at' => now()->toISOString()
                ]
            ]);
            $admin->password = Hash::make('admin123');
            $admin->email_verified_at = now();
            $admin->save();
            
            $this->command->info('Admin user updated with KYC and super admin status.');
            $this->command->info('Email: rizky@worldwideadverts.info');
            $this->command->info('Password: admin123');
        }

        // Create Vikas Jain as marketer admin
        $vikas = User::where('email', 'vikasjain2412@gmail.com')->first();

        if (!$vikas) {
            $vikas = User::create([
                'user_uid' => Str::random(13),
                'first_name' => 'Vikas',
                'last_name' => 'Jain',
                'email' => 'vikasjain2412@gmail.com',
                'password' => Hash::make('vikas123'),
                'email_verified_at' => now(),
                'group_id' => null,
                'timezone' => 'UTC',
                'is_super_admin' => false,
                'can_manage_users' => true,
                'can_manage_categories' => true,
                'can_manage_listings' => true,
                'can_manage_dashboard' => true,
                'can_view_analytics' => true,
                'permissions' => ['manage_users', 'manage_categories', 'manage_listings', 'manage_dashboard', 'view_analytics', 'post_sponsored', 'post_promoted', 'post_admin'],
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_documents' => json_encode([
                    [
                        'type' => 'passport',
                        'path' => 'kyc_documents/vikas_passport.jpg',
                        'original_name' => 'vikas_passport.jpg',
                        'uploaded_at' => now()->toISOString()
                    ]
                ]),
            ]);

            $this->command->info('Vikas Jain (Marketer Admin) created successfully!');
            $this->command->info('Email: vikasjain2412@gmail.com');
            $this->command->info('Password: vikas123');
        } else {
            // Update existing Vikas with marketer permissions
            $vikas->is_super_admin = false;
            $vikas->can_manage_users = true;
            $vikas->can_manage_categories = true;
            $vikas->can_manage_listings = true;
            $vikas->can_manage_dashboard = true;
            $vikas->can_view_analytics = true;
            $vikas->permissions = ['manage_users', 'manage_categories', 'manage_listings', 'manage_dashboard', 'view_analytics', 'post_sponsored', 'post_promoted', 'post_admin'];
            $vikas->kyc_status = 'verified';
            $vikas->kyc_verified_at = now();
            $vikas->email_verified_at = now();
            $vikas->save();
            
            $this->command->info('Vikas Jain updated with marketer admin permissions.');
            $this->command->info('Email: vikasjain2412@gmail.com');
            $this->command->info('Password: vikas123 (if not changed)');
        }

        // Create sample users with different KYC statuses for testing
        $this->createSampleUsers();
    }

    private function createSampleUsers(): void
    {
        // Sample verified user (Admin user, not customer)
        $verifiedUser = User::create([
            'user_uid' => Str::random(13),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@admin.worldwideadverts.info',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'group_id' => null,
            'timezone' => 'UTC',
            'kyc_status' => 'verified',
            'kyc_verified_at' => now()->subDays(5),
            'kyc_documents' => json_encode([
                [
                    'type' => 'passport',
                    'path' => 'kyc_documents/john_passport.jpg',
                    'original_name' => 'john_passport.jpg',
                    'uploaded_at' => now()->subDays(5)->toISOString()
                ],
                [
                    'type' => 'id_document',
                    'path' => 'kyc_documents/john_id.jpg',
                    'original_name' => 'john_id.jpg',
                    'uploaded_at' => now()->subDays(5)->toISOString()
                ]
            ]),
        ]);

        // Sample pending KYC user (Admin user, not customer)
        $pendingUser = User::create([
            'user_uid' => Str::random(13),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@admin.worldwideadverts.info',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'group_id' => null,
            'timezone' => 'UTC',
            'kyc_status' => 'submitted',
            'kyc_documents' => json_encode([
                [
                    'type' => 'national_id',
                    'path' => 'kyc_documents/jane_id.jpg',
                    'original_name' => 'jane_id.jpg',
                    'uploaded_at' => now()->subDay()->toISOString()
                ]
            ]),
        ]);

        // Sample rejected KYC user (Admin user, not customer)
        $rejectedUser = User::create([
            'user_uid' => Str::random(13),
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
            'email' => 'bob.wilson@admin.worldwideadverts.info',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'group_id' => null,
            'timezone' => 'UTC',
            'kyc_status' => 'rejected',
            'kyc_rejection_reason' => 'Document quality too low. Please resubmit with clear, high-resolution documents.',
            'kyc_documents' => json_encode([
                [
                    'type' => 'driver_license',
                    'path' => 'kyc_documents/bob_license.jpg',
                    'original_name' => 'bob_license.jpg',
                    'uploaded_at' => now()->subDays(2)->toISOString()
                ]
            ]),
        ]);

        // Sample user without KYC (Admin user, not customer)
        $noKycUser = User::create([
            'user_uid' => Str::random(13),
            'first_name' => 'Alice',
            'last_name' => 'Brown',
            'email' => 'alice.brown@admin.worldwideadverts.info',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'group_id' => null,
            'timezone' => 'UTC',
            'kyc_status' => 'pending',
        ]);

        $this->command->info('Sample admin users created for KYC testing:');
        $this->command->info('- John Doe (Verified): john.doe@admin.worldwideadverts.info / password123');
        $this->command->info('- Jane Smith (Pending): jane.smith@admin.worldwideadverts.info / password123');
        $this->command->info('- Bob Wilson (Rejected): bob.wilson@admin.worldwideadverts.info / password123');
        $this->command->info('- Alice Brown (No KYC): alice.brown@admin.worldwideadverts.info / password123');
    }
}

