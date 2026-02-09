<?php

namespace Database\Seeders;

use App\Models\DashboardPermission;
use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DashboardPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if dashboard_permissions table exists
        if (!Schema::hasTable('dashboard_permissions')) {
            $this->command->warn('Dashboard permissions table does not exist. Skipping DashboardPermissionSeeder.');
            return;
        }

        // Define dashboard sections
        $dashboardSections = [
            'system_overview',
            'user_analytics', 
            'revenue_analytics',
            'listing_analytics',
            'kyc_analytics',
        ];

        // Define filters available for each section
        $sectionFilters = [
            'system_overview' => ['date_range', 'metrics'],
            'user_analytics' => ['event_type', 'date_range', 'user_group'],
            'revenue_analytics' => ['date_range', 'revenue_source', 'currency'],
            'listing_analytics' => ['event_type', 'date_range', 'category', 'listing_id'],
            'kyc_analytics' => ['date_range', 'kyc_status', 'user_group'],
        ];

        // Get groups
        $groups = Group::all()->keyBy('name');

        // Set permissions for Administrators (full access)
        if (isset($groups['Administrators'])) {
            foreach ($dashboardSections as $section) {
                DashboardPermission::setGroupPermissions(
                    $groups['Administrators']->group_id,
                    $section,
                    true, // can_view
                    true, // can_export
                    $sectionFilters[$section] // all filters
                );
            }
        }

        // Set permissions for Moderators (limited access)
        if (isset($groups['Moderators'])) {
            $moderatorPermissions = [
                'user_analytics' => ['view' => true, 'export' => true, 'filters' => ['event_type', 'date_range']],
                'listing_analytics' => ['view' => true, 'export' => false, 'filters' => ['event_type', 'date_range']],
                'kyc_analytics' => ['view' => true, 'export' => false, 'filters' => ['date_range', 'kyc_status']],
            ];

            foreach ($moderatorPermissions as $section => $permissions) {
                DashboardPermission::setGroupPermissions(
                    $groups['Moderators']->group_id,
                    $section,
                    $permissions['view'],
                    $permissions['export'],
                    $permissions['filters']
                );
            }
        }

        // Set permissions for Editors (content-focused access)
        if (isset($groups['Editors'])) {
            $editorPermissions = [
                'listing_analytics' => ['view' => true, 'export' => true, 'filters' => ['event_type', 'date_range', 'category']],
                'user_analytics' => ['view' => false, 'export' => false, 'filters' => []],
            ];

            foreach ($editorPermissions as $section => $permissions) {
                DashboardPermission::setGroupPermissions(
                    $groups['Editors']->group_id,
                    $section,
                    $permissions['view'],
                    $permissions['export'],
                    $permissions['filters']
                );
            }
        }

        // Set permissions for Support (read-only access)
        if (isset($groups['Support'])) {
            $supportPermissions = [
                'user_analytics' => ['view' => true, 'export' => false, 'filters' => ['date_range']],
                'system_overview' => ['view' => true, 'export' => false, 'filters' => ['date_range']],
            ];

            foreach ($supportPermissions as $section => $permissions) {
                DashboardPermission::setGroupPermissions(
                    $groups['Support']->group_id,
                    $section,
                    $permissions['view'],
                    $permissions['export'],
                    $permissions['filters']
                );
            }
        }

        $this->command->info('Dashboard permissions seeded successfully!');
        $this->command->info('Administrator permissions: Full access to all analytics sections');
        $this->command->info('Moderator permissions: Limited access to user, listing, and KYC analytics');
        $this->command->info('Editor permissions: Access to listing analytics only');
        $this->command->info('Support permissions: Read-only access to user analytics and system overview');
    }
}
