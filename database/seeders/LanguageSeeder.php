<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'name' => 'English',
                'code' => 'en',
                'locale' => 'en_US',
                'flag' => 'us',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Spanish',
                'code' => 'es',
                'locale' => 'es_ES',
                'flag' => 'es',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'French',
                'code' => 'fr',
                'locale' => 'fr_FR',
                'flag' => 'fr',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'German',
                'code' => 'de',
                'locale' => 'de_DE',
                'flag' => 'de',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Italian',
                'code' => 'it',
                'locale' => 'it_IT',
                'flag' => 'it',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Portuguese',
                'code' => 'pt',
                'locale' => 'pt_PT',
                'flag' => 'pt',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Chinese',
                'code' => 'zh',
                'locale' => 'zh_CN',
                'flag' => 'cn',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Japanese',
                'code' => 'ja',
                'locale' => 'ja_JP',
                'flag' => 'jp',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Arabic',
                'code' => 'ar',
                'locale' => 'ar_SA',
                'flag' => 'sa',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Hindi',
                'code' => 'hi',
                'locale' => 'hi_IN',
                'flag' => 'in',
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}

