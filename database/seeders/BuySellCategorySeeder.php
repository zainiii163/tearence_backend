<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BuySellCategory;
use Illuminate\Support\Str;

class BuySellCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'icon' => '💻',
                'description' => 'Phones, laptops, TVs, and other electronic devices',
                'sort_order' => 1,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Smartphones', 'slug' => 'smartphones', 'sort_order' => 1],
                    ['name' => 'Laptops', 'slug' => 'laptops', 'sort_order' => 2],
                    ['name' => 'Tablets', 'slug' => 'tablets', 'sort_order' => 3],
                    ['name' => 'TVs & Home Theater', 'slug' => 'tvs-home-theater', 'sort_order' => 4],
                    ['name' => 'Cameras', 'slug' => 'cameras', 'sort_order' => 5],
                    ['name' => 'Audio & Headphones', 'slug' => 'audio-headphones', 'sort_order' => 6],
                    ['name' => 'Gaming Consoles', 'slug' => 'gaming-consoles', 'sort_order' => 7],
                    ['name' => 'Smart Watches', 'slug' => 'smart-watches', 'sort_order' => 8],
                ]
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'icon' => '🚗',
                'description' => 'Cars, motorcycles, boats, and other vehicles',
                'sort_order' => 2,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Cars', 'slug' => 'cars', 'sort_order' => 1],
                    ['name' => 'Motorcycles', 'slug' => 'motorcycles', 'sort_order' => 2],
                    ['name' => 'Trucks & Vans', 'slug' => 'trucks-vans', 'sort_order' => 3],
                    ['name' => 'Boats', 'slug' => 'boats', 'sort_order' => 4],
                    ['name' => 'RVs & Campers', 'slug' => 'rvs-campers', 'sort_order' => 5],
                    ['name' => 'Parts & Accessories', 'slug' => 'parts-accessories', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'icon' => '🏠',
                'description' => 'Furniture, appliances, tools, and garden supplies',
                'sort_order' => 3,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Furniture', 'slug' => 'furniture', 'sort_order' => 1],
                    ['name' => 'Appliances', 'slug' => 'appliances', 'sort_order' => 2],
                    ['name' => 'Garden & Outdoor', 'slug' => 'garden-outdoor', 'sort_order' => 3],
                    ['name' => 'Home Decor', 'slug' => 'home-decor', 'sort_order' => 4],
                    ['name' => 'Kitchen & Dining', 'slug' => 'kitchen-dining', 'sort_order' => 5],
                    ['name' => 'Bedding & Bath', 'slug' => 'bedding-bath', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Fashion & Accessories',
                'slug' => 'fashion-accessories',
                'icon' => '👕',
                'description' => 'Clothing, shoes, jewelry, and fashion accessories',
                'sort_order' => 4,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => "Men's Clothing", 'slug' => 'mens-clothing', 'sort_order' => 1],
                    ['name' => "Women's Clothing", 'slug' => 'womens-clothing', 'sort_order' => 2],
                    ['name' => 'Shoes', 'slug' => 'shoes', 'sort_order' => 3],
                    ['name' => 'Jewelry & Watches', 'slug' => 'jewelry-watches', 'sort_order' => 4],
                    ['name' => 'Bags & Accessories', 'slug' => 'bags-accessories', 'sort_order' => 5],
                    ['name' => 'Kids Clothing', 'slug' => 'kids-clothing', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'icon' => '⚽',
                'description' => 'Sports equipment, fitness gear, and outdoor activities',
                'sort_order' => 5,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Fitness Equipment', 'slug' => 'fitness-equipment', 'sort_order' => 1],
                    ['name' => 'Outdoor Gear', 'slug' => 'outdoor-gear', 'sort_order' => 2],
                    ['name' => 'Team Sports', 'slug' => 'team-sports', 'sort_order' => 3],
                    ['name' => 'Water Sports', 'slug' => 'water-sports', 'sort_order' => 4],
                    ['name' => 'Winter Sports', 'slug' => 'winter-sports', 'sort_order' => 5],
                    ['name' => 'Cycling', 'slug' => 'cycling', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'icon' => '📚',
                'description' => 'Books, movies, music, games, and other media',
                'sort_order' => 6,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Books', 'slug' => 'books', 'sort_order' => 1],
                    ['name' => 'Movies & TV', 'slug' => 'movies-tv', 'sort_order' => 2],
                    ['name' => 'Music', 'slug' => 'music', 'sort_order' => 3],
                    ['name' => 'Video Games', 'slug' => 'video-games', 'sort_order' => 4],
                    ['name' => 'Musical Instruments', 'slug' => 'musical-instruments', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Baby & Kids',
                'slug' => 'baby-kids',
                'icon' => '👶',
                'description' => 'Baby products, kids toys, and children items',
                'sort_order' => 7,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Baby Gear', 'slug' => 'baby-gear', 'sort_order' => 1],
                    ['name' => 'Toys & Games', 'slug' => 'toys-games', 'sort_order' => 2],
                    ['name' => 'Kids Wear', 'slug' => 'kids-wear', 'sort_order' => 3],
                    ['name' => 'Nursery Furniture', 'slug' => 'nursery-furniture', 'sort_order' => 4],
                    ['name' => 'Strollers & Car Seats', 'slug' => 'strollers-car-seats', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Tools & Hardware',
                'slug' => 'tools-hardware',
                'icon' => '🔧',
                'description' => 'Power tools, hand tools, and hardware supplies',
                'sort_order' => 8,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Power Tools', 'slug' => 'power-tools', 'sort_order' => 1],
                    ['name' => 'Hand Tools', 'slug' => 'hand-tools', 'sort_order' => 2],
                    ['name' => 'Garden Tools', 'slug' => 'garden-tools', 'sort_order' => 3],
                    ['name' => 'Building Materials', 'slug' => 'building-materials', 'sort_order' => 4],
                    ['name' => 'Hardware', 'slug' => 'hardware', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Business & Industrial',
                'slug' => 'business-industrial',
                'icon' => '🏢',
                'description' => 'Business equipment, industrial machinery, and commercial supplies',
                'sort_order' => 9,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Office Equipment', 'slug' => 'office-equipment', 'sort_order' => 1],
                    ['name' => 'Restaurant Equipment', 'slug' => 'restaurant-equipment', 'sort_order' => 2],
                    ['name' => 'Medical Equipment', 'slug' => 'medical-equipment', 'sort_order' => 3],
                    ['name' => 'Manufacturing Equipment', 'slug' => 'manufacturing-equipment', 'sort_order' => 4],
                    ['name' => 'Construction Equipment', 'slug' => 'construction-equipment', 'sort_order' => 5],
                ]
            ],
            [
                'name' => 'Collectibles & Art',
                'slug' => 'collectibles-art',
                'icon' => '🎨',
                'description' => 'Artwork, antiques, collectibles, and memorabilia',
                'sort_order' => 10,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Art', 'slug' => 'art', 'sort_order' => 1],
                    ['name' => 'Antiques', 'slug' => 'antiques', 'sort_order' => 2],
                    ['name' => 'Coins & Currency', 'slug' => 'coins-currency', 'sort_order' => 3],
                    ['name' => 'Stamps', 'slug' => 'stamps', 'sort_order' => 4],
                    ['name' => 'Comics', 'slug' => 'comics', 'sort_order' => 5],
                    ['name' => 'Trading Cards', 'slug' => 'trading-cards', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Pets & Supplies',
                'slug' => 'pets-supplies',
                'icon' => '🐕',
                'description' => 'Pets, pet supplies, and animal accessories',
                'sort_order' => 11,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Dogs', 'slug' => 'dogs', 'sort_order' => 1],
                    ['name' => 'Cats', 'slug' => 'cats', 'sort_order' => 2],
                    ['name' => 'Birds', 'slug' => 'birds', 'sort_order' => 3],
                    ['name' => 'Fish & Aquariums', 'slug' => 'fish-aquariums', 'sort_order' => 4],
                    ['name' => 'Pet Supplies', 'slug' => 'pet-supplies', 'sort_order' => 5],
                    ['name' => 'Other Pets', 'slug' => 'other-pets', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'icon' => '🔧',
                'description' => 'Professional services and skilled labor',
                'sort_order' => 12,
                'level' => 1,
                'is_active' => true,
                'subcategories' => [
                    ['name' => 'Home Services', 'slug' => 'home-services', 'sort_order' => 1],
                    ['name' => 'Automotive Services', 'slug' => 'automotive-services', 'sort_order' => 2],
                    ['name' => 'Computer Services', 'slug' => 'computer-services', 'sort_order' => 3],
                    ['name' => 'Event Services', 'slug' => 'event-services', 'sort_order' => 4],
                    ['name' => 'Tutoring & Lessons', 'slug' => 'tutoring-lessons', 'sort_order' => 5],
                    ['name' => 'Beauty & Wellness', 'slug' => 'beauty-wellness', 'sort_order' => 6],
                ]
            ],
            [
                'name' => 'Other Items',
                'slug' => 'other-items',
                'icon' => '📦',
                'description' => 'Miscellaneous items that don\'t fit in other categories',
                'sort_order' => 13,
                'level' => 1,
                'is_active' => true,
                'subcategories' => []
            ],
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'] ?? [];
            unset($categoryData['subcategories']);

            $parentCategory = BuySellCategory::create($categoryData);

            foreach ($subcategories as $subcategoryData) {
                BuySellCategory::create(array_merge($subcategoryData, [
                    'parent_id' => $parentCategory->id,
                    'level' => 2,
                    'is_active' => true,
                ]));
            }
        }
    }
}
