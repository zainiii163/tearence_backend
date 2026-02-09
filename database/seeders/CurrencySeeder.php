<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.000000,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 0.920000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'British Pound',
                'code' => 'GBP',
                'symbol' => '£',
                'exchange_rate' => 0.790000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Japanese Yen',
                'code' => 'JPY',
                'symbol' => '¥',
                'exchange_rate' => 149.500000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Canadian Dollar',
                'code' => 'CAD',
                'symbol' => 'C$',
                'exchange_rate' => 1.350000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Australian Dollar',
                'code' => 'AUD',
                'symbol' => 'A$',
                'exchange_rate' => 1.520000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Swiss Franc',
                'code' => 'CHF',
                'symbol' => 'CHF',
                'exchange_rate' => 0.880000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Chinese Yuan',
                'code' => 'CNY',
                'symbol' => '¥',
                'exchange_rate' => 7.250000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Indian Rupee',
                'code' => 'INR',
                'symbol' => '₹',
                'exchange_rate' => 83.000000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Singapore Dollar',
                'code' => 'SGD',
                'symbol' => 'S$',
                'exchange_rate' => 1.340000,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}

