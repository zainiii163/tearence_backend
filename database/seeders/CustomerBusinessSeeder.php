<?php

namespace Database\Seeders;

use App\Models\CustomerBusiness;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerBusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping CustomerBusinessSeeder.');
            return;
        }

        $businesses = [
            [
                'customer_id' => $customers[0]->customer_id,
                'slug' => Str::slug('Tech Solutions Inc'),
                'business_name' => 'Tech Solutions Inc',
                'business_phone_number' => '+1-555-0101',
                'business_address' => '123 Tech Street, San Francisco, CA 94102',
                'business_email' => 'contact@techsolutions.com',
                'business_website' => 'https://techsolutions.com',
                'business_owner' => 'John Doe',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'slug' => Str::slug('Digital Marketing Agency'),
                'business_name' => 'Digital Marketing Agency',
                'business_phone_number' => '+1-555-0202',
                'business_address' => '456 Marketing Ave, New York, NY 10001',
                'business_email' => 'info@digitalmarketing.com',
                'business_website' => 'https://digitalmarketing.com',
                'business_owner' => 'Jane Smith',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'slug' => Str::slug('Creative Design Studio'),
                'business_name' => 'Creative Design Studio',
                'business_phone_number' => '+1-555-0303',
                'business_address' => '789 Design Blvd, Los Angeles, CA 90001',
                'business_email' => 'hello@creativedesign.com',
                'business_website' => 'https://creativedesign.com',
                'business_owner' => 'Michael Johnson',
                'status' => 'active',
            ],
        ];

        foreach ($businesses as $business) {
            CustomerBusiness::create($business);
        }
    }
}

