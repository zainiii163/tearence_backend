<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleMake;
use App\Models\VehicleModel;

class VehicleMakeAndModelSeeder extends Seeder
{
    public function run(): void
    {
        $makes = [
            [
                'name' => 'Toyota',
                'slug' => 'toyota',
                'country' => 'Japan',
                'is_active' => true,
                'models' => [
                    ['name' => 'Corolla', 'slug' => 'corolla'],
                    ['name' => 'Camry', 'slug' => 'camry'],
                    ['name' => 'RAV4', 'slug' => 'rav4'],
                    ['name' => 'Hilux', 'slug' => 'hilux'],
                    ['name' => 'Land Cruiser', 'slug' => 'land-cruiser'],
                    ['name' => 'Prius', 'slug' => 'prius'],
                    ['name' => 'Yaris', 'slug' => 'yaris'],
                    ['name' => 'Avalon', 'slug' => 'avalon'],
                ],
            ],
            [
                'name' => 'BMW',
                'slug' => 'bmw',
                'country' => 'Germany',
                'is_active' => true,
                'models' => [
                    ['name' => '3 Series', 'slug' => '3-series'],
                    ['name' => '5 Series', 'slug' => '5-series'],
                    ['name' => '7 Series', 'slug' => '7-series'],
                    ['name' => 'X3', 'slug' => 'x3'],
                    ['name' => 'X5', 'slug' => 'x5'],
                    ['name' => 'M3', 'slug' => 'm3'],
                    ['name' => 'M5', 'slug' => 'm5'],
                    ['name' => 'i3', 'slug' => 'i3'],
                ],
            ],
            [
                'name' => 'Mercedes-Benz',
                'slug' => 'mercedes-benz',
                'country' => 'Germany',
                'is_active' => true,
                'models' => [
                    ['name' => 'C-Class', 'slug' => 'c-class'],
                    ['name' => 'E-Class', 'slug' => 'e-class'],
                    ['name' => 'S-Class', 'slug' => 's-class'],
                    ['name' => 'GLC', 'slug' => 'glc'],
                    ['name' => 'GLE', 'slug' => 'gle'],
                    ['name' => 'AMG GT', 'slug' => 'amg-gt'],
                    ['name' => 'A-Class', 'slug' => 'a-class'],
                    ['name' => 'G-Class', 'slug' => 'g-class'],
                ],
            ],
            [
                'name' => 'Audi',
                'slug' => 'audi',
                'country' => 'Germany',
                'is_active' => true,
                'models' => [
                    ['name' => 'A3', 'slug' => 'a3'],
                    ['name' => 'A4', 'slug' => 'a4'],
                    ['name' => 'A6', 'slug' => 'a6'],
                    ['name' => 'Q3', 'slug' => 'q3'],
                    ['name' => 'Q5', 'slug' => 'q5'],
                    ['name' => 'Q7', 'slug' => 'q7'],
                    ['name' => 'R8', 'slug' => 'r8'],
                    ['name' => 'e-tron', 'slug' => 'etron'],
                ],
            ],
            [
                'name' => 'Honda',
                'slug' => 'honda',
                'country' => 'Japan',
                'is_active' => true,
                'models' => [
                    ['name' => 'Civic', 'slug' => 'civic'],
                    ['name' => 'Accord', 'slug' => 'accord'],
                    ['name' => 'CR-V', 'slug' => 'cr-v'],
                    ['name' => 'Pilot', 'slug' => 'pilot'],
                    ['name' => 'Fit', 'slug' => 'fit'],
                    ['name' => 'HR-V', 'slug' => 'hr-v'],
                    ['name' => 'Odyssey', 'slug' => 'odyssey'],
                    ['name' => 'NSX', 'slug' => 'nsx'],
                ],
            ],
            [
                'name' => 'Ford',
                'slug' => 'ford',
                'country' => 'USA',
                'is_active' => true,
                'models' => [
                    ['name' => 'F-150', 'slug' => 'f-150'],
                    ['name' => 'Mustang', 'slug' => 'mustang'],
                    ['name' => 'Explorer', 'slug' => 'explorer'],
                    ['name' => 'Escape', 'slug' => 'escape'],
                    ['name' => 'Focus', 'slug' => 'focus'],
                    ['name' => 'Fusion', 'slug' => 'fusion'],
                    ['name' => 'Ranger', 'slug' => 'ranger'],
                    ['name' => 'Bronco', 'slug' => 'bronco'],
                ],
            ],
            [
                'name' => 'Volkswagen',
                'slug' => 'volkswagen',
                'country' => 'Germany',
                'is_active' => true,
                'models' => [
                    ['name' => 'Golf', 'slug' => 'golf'],
                    ['name' => 'Passat', 'slug' => 'passat'],
                    ['name' => 'Jetta', 'slug' => 'jetta'],
                    ['name' => 'Tiguan', 'slug' => 'tiguan'],
                    ['name' => 'Atlas', 'slug' => 'atlas'],
                    ['name' => 'ID.4', 'slug' => 'id-4'],
                    ['name' => 'Polo', 'slug' => 'polo'],
                    ['name' => 'Beetle', 'slug' => 'beetle'],
                ],
            ],
            [
                'name' => 'Nissan',
                'slug' => 'nissan',
                'country' => 'Japan',
                'is_active' => true,
                'models' => [
                    ['name' => 'Altima', 'slug' => 'altima'],
                    ['name' => 'Sentra', 'slug' => 'sentra'],
                    ['name' => 'Rogue', 'slug' => 'rogue'],
                    ['name' => 'Pathfinder', 'slug' => 'pathfinder'],
                    ['name' => '370Z', 'slug' => '370z'],
                    ['name' => 'GT-R', 'slug' => 'gt-r'],
                    ['name' => 'Leaf', 'slug' => 'leaf'],
                    ['name' => 'Frontier', 'slug' => 'frontier'],
                ],
            ],
            [
                'name' => 'Hyundai',
                'slug' => 'hyundai',
                'country' => 'South Korea',
                'is_active' => true,
                'models' => [
                    ['name' => 'Elantra', 'slug' => 'elantra'],
                    ['name' => 'Sonata', 'slug' => 'sonata'],
                    ['name' => 'Tucson', 'slug' => 'tucson'],
                    ['name' => 'Santa Fe', 'slug' => 'santa-fe'],
                    ['name' => 'Kona', 'slug' => 'kona'],
                    ['name' => 'Veloster', 'slug' => 'veloster'],
                    ['name' => 'Ioniq', 'slug' => 'ioniq'],
                    ['name' => 'Genesis', 'slug' => 'genesis'],
                ],
            ],
            [
                'name' => 'Kia',
                'slug' => 'kia',
                'country' => 'South Korea',
                'is_active' => true,
                'models' => [
                    ['name' => 'Forte', 'slug' => 'forte'],
                    ['name' => 'Optima', 'slug' => 'optima'],
                    ['name' => 'Sportage', 'slug' => 'sportage'],
                    ['name' => 'Sorento', 'slug' => 'sorento'],
                    ['name' => 'Soul', 'slug' => 'soul'],
                    ['name' => 'Telluride', 'slug' => 'telluride'],
                    ['name' => 'Stinger', 'slug' => 'stinger'],
                    ['name' => 'Seltos', 'slug' => 'seltos'],
                ],
            ],
        ];

        foreach ($makes as $makeData) {
            $models = $makeData['models'];
            unset($makeData['models']);

            $make = VehicleMake::create($makeData);

            foreach ($models as $modelData) {
                VehicleModel::create([
                    'make_id' => $make->id,
                    'name' => $modelData['name'],
                    'slug' => $modelData['slug'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
