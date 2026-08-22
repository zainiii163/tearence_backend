<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TeamRoleSeeder extends Seeder
{
    /**
     * Clive department teams + sub-roles for WWA admin portal.
     */
    public function run(): void
    {
        if (!Schema::hasTable('group')) {
            $this->command?->warn('group table missing — skip TeamRoleSeeder');
            return;
        }

        $tree = [
            'admins' => [
                'name' => 'Admins',
                'description' => 'Platform administration team',
                'roles' => [
                    'super-admin' => [
                        'name' => 'Super Admin',
                        'description' => 'Full system access',
                        'flags' => ['all' => true],
                    ],
                    'platform-admin' => [
                        'name' => 'Platform Admin',
                        'description' => 'Manage users, listings, dashboard',
                        'flags' => [
                            'can_manage_users' => true,
                            'can_manage_categories' => true,
                            'can_manage_listings' => true,
                            'can_manage_dashboard' => true,
                            'can_view_analytics' => true,
                        ],
                    ],
                ],
            ],
            'hr' => [
                'name' => 'HR',
                'description' => 'Human resources team',
                'roles' => [
                    'payroll-admin' => [
                        'name' => 'Payroll Admin',
                        'description' => 'Payroll and compensation',
                        'flags' => ['can_manage_users' => true, 'can_view_analytics' => true],
                        'permissions' => ['hr.payroll', 'hr.view'],
                    ],
                    'services-admin' => [
                        'name' => 'Services Admin',
                        'description' => 'HR services and employee services',
                        'flags' => ['can_manage_users' => true],
                        'permissions' => ['hr.services', 'hr.view'],
                    ],
                    'recruitment-admin' => [
                        'name' => 'Recruitment Admin',
                        'description' => 'Hiring and recruitment',
                        'flags' => ['can_manage_users' => true, 'can_manage_listings' => true],
                        'permissions' => ['hr.recruitment', 'hr.view'],
                    ],
                ],
            ],
            'accountants' => [
                'name' => 'Accountants',
                'description' => 'Accounts and finance team',
                'roles' => [
                    'billing-admin' => [
                        'name' => 'Billing Admin',
                        'description' => 'Customer billing',
                        'flags' => ['can_view_analytics' => true, 'can_manage_dashboard' => true],
                        'permissions' => ['accounts.billing', 'accounts.view'],
                    ],
                    'payroll-accounts' => [
                        'name' => 'Payroll Accounts',
                        'description' => 'Payroll accounting',
                        'flags' => ['can_view_analytics' => true],
                        'permissions' => ['accounts.payroll', 'accounts.view'],
                    ],
                    'invoicing-admin' => [
                        'name' => 'Invoicing Admin',
                        'description' => 'Invoices and receipts',
                        'flags' => ['can_view_analytics' => true, 'can_manage_dashboard' => true],
                        'permissions' => ['accounts.invoicing', 'accounts.view'],
                    ],
                ],
            ],
            'legal' => [
                'name' => 'Legal',
                'description' => 'Legal and compliance team',
                'roles' => [
                    'compliance-admin' => [
                        'name' => 'Compliance Admin',
                        'description' => 'Policy and compliance',
                        'flags' => ['can_manage_listings' => true, 'can_view_analytics' => true],
                        'permissions' => ['legal.compliance', 'legal.view'],
                    ],
                    'contracts-admin' => [
                        'name' => 'Contracts Admin',
                        'description' => 'Contracts and agreements',
                        'flags' => ['can_manage_users' => true],
                        'permissions' => ['legal.contracts', 'legal.view'],
                    ],
                ],
            ],
            'marketing' => [
                'name' => 'Marketing',
                'description' => 'Marketing team',
                'roles' => [
                    'campaign-admin' => [
                        'name' => 'Campaign Admin',
                        'description' => 'Campaigns and promotions',
                        'flags' => ['can_manage_listings' => true, 'can_view_analytics' => true],
                        'permissions' => ['marketing.campaigns', 'marketing.view'],
                    ],
                    'content-admin' => [
                        'name' => 'Content Admin',
                        'description' => 'Marketing content',
                        'flags' => ['can_manage_categories' => true, 'can_manage_listings' => true],
                        'permissions' => ['marketing.content', 'marketing.view'],
                    ],
                ],
            ],
            'sales' => [
                'name' => 'Sales',
                'description' => 'Sales team',
                'roles' => [
                    'sales-admin' => [
                        'name' => 'Sales Admin',
                        'description' => 'Sales operations',
                        'flags' => ['can_manage_users' => true, 'can_view_analytics' => true],
                        'permissions' => ['sales.manage', 'sales.view'],
                    ],
                    'lead-admin' => [
                        'name' => 'Lead Admin',
                        'description' => 'Leads and pipeline',
                        'flags' => ['can_manage_users' => true, 'can_view_analytics' => true],
                        'permissions' => ['sales.leads', 'sales.view'],
                    ],
                ],
            ],
            'advertising' => [
                'name' => 'Advertising',
                'description' => 'Advertising and upsells team',
                'roles' => [
                    'promo-admin' => [
                        'name' => 'Promo Admin',
                        'description' => 'Promo pricing and reward codes',
                        'flags' => ['can_manage_listings' => true, 'can_view_analytics' => true, 'can_manage_dashboard' => true],
                        'permissions' => ['ads.promo', 'ads.view'],
                    ],
                    'upsell-admin' => [
                        'name' => 'Upsell Admin',
                        'description' => 'Featured / sponsored / promoted upsels',
                        'flags' => ['can_manage_listings' => true, 'can_view_analytics' => true],
                        'permissions' => ['ads.upsell', 'ads.view'],
                    ],
                ],
            ],
            'moderation' => [
                'name' => 'Moderation',
                'description' => 'Content moderation team',
                'roles' => [
                    'content-moderator' => [
                        'name' => 'Content Moderator',
                        'description' => 'Moderate listings and posts',
                        'flags' => ['can_manage_listings' => true, 'can_manage_categories' => true],
                        'permissions' => ['moderation.content', 'moderation.view'],
                    ],
                    'reports-moderator' => [
                        'name' => 'Reports Moderator',
                        'description' => 'User reports and disputes',
                        'flags' => ['can_manage_listings' => true, 'can_manage_users' => true],
                        'permissions' => ['moderation.reports', 'moderation.view'],
                    ],
                ],
            ],
            'support' => [
                'name' => 'Support',
                'description' => 'Customer support team',
                'roles' => [
                    'support-agent' => [
                        'name' => 'Support Agent',
                        'description' => 'Handle support tickets',
                        'flags' => ['can_manage_users' => true],
                        'permissions' => ['support.tickets', 'support.view'],
                    ],
                    'support-lead' => [
                        'name' => 'Support Lead',
                        'description' => 'Lead support operations',
                        'flags' => [
                            'can_manage_users' => true,
                            'can_manage_listings' => true,
                            'can_view_analytics' => true,
                        ],
                        'permissions' => ['support.lead', 'support.view'],
                    ],
                ],
            ],
            'it' => [
                'name' => 'IT',
                'description' => 'IT / security — login monitoring and infrastructure',
                'roles' => [
                    'security-analyst' => [
                        'name' => 'Security Analyst',
                        'description' => 'View all login activity and security alerts',
                        'flags' => [
                            'can_view_analytics' => true,
                        ],
                        'permissions' => ['security.logs', 'security.alerts'],
                    ],
                    'it-admin' => [
                        'name' => 'IT Admin',
                        'description' => 'IT department lead — full security log access',
                        'flags' => [
                            'can_view_analytics' => true,
                            'can_manage_dashboard' => true,
                        ],
                        'permissions' => ['security.logs', 'security.alerts', 'security.manage'],
                    ],
                ],
            ],
        ];

        foreach ($tree as $teamSlug => $teamData) {
            $team = Group::updateOrCreate(
                ['slug' => $teamSlug],
                [
                    'name' => $teamData['name'],
                    'description' => $teamData['description'],
                    'type' => 'team',
                    'parent_id' => null,
                    'is_active' => true,
                    'can_manage_users' => false,
                    'can_manage_categories' => false,
                    'can_manage_listings' => false,
                    'can_manage_dashboard' => false,
                    'can_view_analytics' => false,
                    'permissions' => ['team.' . $teamSlug],
                ]
            );

            foreach ($teamData['roles'] as $roleSlug => $roleData) {
                $flags = $roleData['flags'] ?? [];
                $all = !empty($flags['all']);
                Group::updateOrCreate(
                    ['slug' => $teamSlug . '-' . $roleSlug],
                    [
                        'name' => $roleData['name'],
                        'description' => $roleData['description'] ?? '',
                        'type' => 'role',
                        'parent_id' => $team->group_id,
                        'is_active' => true,
                        'can_manage_users' => $all || !empty($flags['can_manage_users']),
                        'can_manage_categories' => $all || !empty($flags['can_manage_categories']),
                        'can_manage_listings' => $all || !empty($flags['can_manage_listings']),
                        'can_manage_dashboard' => $all || !empty($flags['can_manage_dashboard']),
                        'can_view_analytics' => $all || !empty($flags['can_view_analytics']),
                        'permissions' => $roleData['permissions'] ?? [],
                    ]
                );
            }
        }

        // Map legacy flat groups into teams when they still have no slug
        $legacyMap = [
            'Administrators' => 'admins',
            'Moderators' => 'moderation',
            'Editors' => 'marketing',
            'Support' => 'support',
        ];
        foreach ($legacyMap as $legacyName => $teamSlug) {
            $legacy = Group::where('name', $legacyName)->whereNull('slug')->first();
            if (!$legacy) {
                continue;
            }
            $team = Group::where('slug', $teamSlug)->first();
            if (!$team) {
                continue;
            }
            $legacy->update([
                'slug' => 'legacy-' . Str::slug($legacyName),
                'type' => 'role',
                'parent_id' => $team->group_id,
                'description' => trim(($legacy->description ?? '') . ' (legacy role)'),
            ]);
        }

        $this->command?->info('Team roles seeded (HR, Accountants, Legal, Marketing, Sales, Advertising, Moderation, Support, Admins).');
    }
}
