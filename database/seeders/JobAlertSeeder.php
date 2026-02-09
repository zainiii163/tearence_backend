<?php

namespace Database\Seeders;

use App\Models\JobAlert;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Seeder;

class JobAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();
        $categories = Category::whereNull('parent_id')->take(2)->get();
        $locations = Location::take(3)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping JobAlertSeeder.');
            return;
        }

        $alerts = [
            [
                'customer_id' => $customers[0]->customer_id,
                'name' => 'PHP Developer Jobs',
                'keywords' => json_encode(['PHP', 'Laravel', 'Backend', 'Developer']),
                'location_id' => $locations->count() > 0 ? $locations[0]->location_id : null,
                'category_id' => $categories->count() > 0 ? $categories[0]->category_id : null,
                'job_type' => json_encode(['full-time', 'part-time']),
                'salary_min' => 60000,
                'salary_max' => 100000,
                'frequency' => 'daily',
                'is_active' => true,
                'notification_email' => $customers[0]->email,
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'name' => 'Frontend Developer Alerts',
                'keywords' => json_encode(['React', 'Vue.js', 'Frontend', 'JavaScript']),
                'location_id' => $locations->count() > 1 ? $locations[1]->location_id : null,
                'category_id' => $categories->count() > 1 ? $categories[1]->category_id : null,
                'job_type' => json_encode(['full-time', 'contract']),
                'salary_min' => 70000,
                'salary_max' => 110000,
                'frequency' => 'weekly',
                'is_active' => true,
                'notification_email' => $customers->count() > 1 ? $customers[1]->email : $customers[0]->email,
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'name' => 'Remote Developer Positions',
                'keywords' => json_encode(['Remote', 'Developer', 'Software Engineer']),
                'location_id' => null,
                'category_id' => $categories->count() > 0 ? $categories[0]->category_id : null,
                'job_type' => json_encode(['full-time', 'remote']),
                'salary_min' => 80000,
                'salary_max' => 120000,
                'frequency' => 'instant',
                'is_active' => true,
                'notification_email' => $customers->count() > 2 ? $customers[2]->email : $customers[0]->email,
            ],
        ];

        foreach ($alerts as $alert) {
            JobAlert::create($alert);
        }
    }
}

