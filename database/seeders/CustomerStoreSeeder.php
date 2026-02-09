<?php

namespace Database\Seeders;

use App\Models\CustomerStore;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping CustomerStoreSeeder.');
            return;
        }

        $stores = [
            [
                'customer_id' => $customers[0]->customer_id,
                'slug' => Str::slug('Premium Store'),
                'store_name' => 'Premium Store',
                'company_name' => 'Premium Store LLC',
                'company_no' => 'COMP001',
                'vat' => 'VAT-001',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'slug' => Str::slug('Global Market'),
                'store_name' => 'Global Market',
                'company_name' => 'Global Market Inc',
                'company_no' => 'COMP002',
                'vat' => 'VAT-002',
                'status' => 'active',
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'slug' => Str::slug('Mega Shop'),
                'store_name' => 'Mega Shop',
                'company_name' => 'Mega Shop Corporation',
                'company_no' => 'COMP003',
                'vat' => 'VAT-003',
                'status' => 'active',
            ],
        ];

        foreach ($stores as $store) {
            CustomerStore::create($store);
        }
    }
}

