<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Support\Facades\DB;

class VehicleMakeModelSeeder extends Seeder
{
    public function run(): void
    {
        $makes = [
            'Toyota' => [
                'Corolla', 'Camry', 'Prius', 'RAV4', 'Highlander', 'Tacoma', 'Tundra', 'Sienna', 'Yaris', 'Avalon'
            ],
            'Honda' => [
                'Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'Fit', 'HR-V', 'Passport', 'Ridgeline', 'Insight'
            ],
            'Ford' => [
                'F-150', 'Mustang', 'Explorer', 'Escape', 'Fusion', 'Focus', 'Edge', 'Expedition', 'Transit', 'Ranger'
            ],
            'Chevrolet' => [
                'Silverado', 'Malibu', 'Equinox', 'Tahoe', 'Suburban', 'Traverse', 'Camaro', 'Corvette', 'Spark', 'Sonic'
            ],
            'BMW' => [
                '3 Series', '5 Series', '7 Series', 'X3', 'X5', 'X1', 'X7', '2 Series', '4 Series', '6 Series'
            ],
            'Mercedes-Benz' => [
                'C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'A-Class', 'CLA', 'CLS', 'GLS'
            ],
            'Audi' => [
                'A3', 'A4', 'A6', 'A8', 'Q3', 'Q5', 'Q7', 'Q2', 'Q8', 'e-tron'
            ],
            'Volkswagen' => [
                'Golf', 'Jetta', 'Passat', 'Tiguan', 'Atlas', 'Polo', 'Touareg', 'Beetle', 'Arteon', 'ID.4'
            ],
            'Nissan' => [
                'Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Murano', 'Frontier', 'Titan', 'Leaf', 'Maxima', 'Kicks'
            ],
            'Hyundai' => [
                'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade', 'Venue', 'Kona', 'Accent', 'Ioniq', 'Veloster'
            ],
            'Kia' => [
                'Optima', 'Forte', 'Sportage', 'Sorento', 'Telluride', 'Soul', 'Rio', 'Stinger', 'Carnival', 'Niro'
            ],
            'Mazda' => [
                'Mazda3', 'Mazda6', 'CX-3', 'CX-5', 'CX-9', 'MX-5 Miata', 'CX-30', 'Mazda2', 'CX-8', 'RX-8'
            ],
            'Subaru' => [
                'Impreza', 'Legacy', 'Outback', 'Forester', 'Crosstrek', 'Ascent', 'WRX', 'BRZ', 'Tribeca', 'Baja'
            ],
            'Lexus' => [
                'ES', 'RX', 'NX', 'UX', 'LS', 'IS', 'GS', 'GX', 'LX', 'LC'
            ],
            'Tesla' => [
                'Model S', 'Model 3', 'Model X', 'Model Y', 'Cybertruck', 'Roadster', 'Semi'
            ],
            'Land Rover' => [
                'Range Rover', 'Discovery', 'Defender', 'Evoque', 'Sport', 'Velar', 'Freelander', 'LR3', 'LR4'
            ],
            'Jaguar' => [
                'XE', 'XF', 'XJ', 'F-Type', 'E-Pace', 'F-Pace', 'I-Pace', 'X-Type', 'S-Type', 'XK'
            ],
            'Volvo' => [
                'XC40', 'XC60', 'XC90', 'S60', 'S90', 'V60', 'V90', 'V40', 'C30', 'S40'
            ],
            'Porsche' => [
                '911', 'Cayenne', 'Macan', 'Panamera', 'Taycan', '718 Cayman', '718 Boxster', '918 Spyder'
            ],
            'Ferrari' => [
                '488', 'F8', 'Portofino', 'Roma', 'SF90', '812', 'GTC4Lusso', 'Monza', '296 GTB'
            ],
            'Lamborghini' => [
                'Huracán', 'Aventador', 'Urus', 'Gallardo', 'Murciélago', 'Sian', 'Countach', 'Diablo'
            ],
        ];

        foreach ($makes as $makeName => $models) {
            $make = VehicleMake::create([
                'name' => $makeName,
                'slug' => strtolower(str_replace(' ', '-', $makeName)),
                'is_active' => true,
                'sort_order' => 0,
            ]);

            foreach ($models as $modelName) {
                VehicleModel::create([
                    'make_id' => $make->id,
                    'name' => $modelName,
                    'slug' => strtolower(str_replace(' ', '-', $modelName)),
                    'is_active' => true,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
