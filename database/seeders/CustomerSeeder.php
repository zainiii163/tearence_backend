<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCurrency = Currency::where('code', 'USD')->first();
        $currencyId = $defaultCurrency ? $defaultCurrency->currency_id : null;

        $customers = [
            [
                'customer_uid' => Str::random(10),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'password_hash' => Hash::make('password123'),
                'currency_id' => $currencyId,
                'affiliated_members' => 0,
            ],
            [
                'customer_uid' => Str::random(10),
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'password_hash' => Hash::make('password123'),
                'currency_id' => $currencyId,
                'affiliated_members' => 0,
            ],
            [
                'customer_uid' => Str::random(10),
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'password_hash' => Hash::make('password123'),
                'currency_id' => $currencyId,
                'affiliated_members' => 0,
            ],
            [
                'customer_uid' => Str::random(10),
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@example.com',
                'password_hash' => Hash::make('password123'),
                'currency_id' => $currencyId,
                'affiliated_members' => 0,
            ],
            [
                'customer_uid' => Str::random(10),
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@example.com',
                'password_hash' => Hash::make('password123'),
                'currency_id' => $currencyId,
                'affiliated_members' => 0,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}

