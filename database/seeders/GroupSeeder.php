<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if group table exists
        if (!Schema::hasTable('group')) {
            $this->command->warn('Group table does not exist. Skipping GroupSeeder.');
            return;
        }

        $groups = [
            [
                'name' => 'Administrators',
                'description' => 'Full system administrators with all privileges',
                'is_active' => true,
            ],
            [
                'name' => 'Moderators',
                'description' => 'Content moderators with limited administrative access',
                'is_active' => true,
            ],
            [
                'name' => 'Editors',
                'description' => 'Content editors with content management permissions',
                'is_active' => true,
            ],
            [
                'name' => 'Support',
                'description' => 'Customer support team with read-only access',
                'is_active' => true,
            ],
        ];

        foreach ($groups as $group) {
            // Check if group already exists
            $exists = Group::where('name', $group['name'])->exists();

            if (!$exists) {
                Group::create($group);
            }
        }
    }
}

