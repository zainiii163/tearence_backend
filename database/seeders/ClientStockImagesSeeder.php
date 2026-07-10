<?php

namespace Database\Seeders;

use App\Models\ImagesAdvert;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientStockImagesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'rizky@worldwideadverts.info')->first()
            ?? User::where('is_super_admin', true)->first()
            ?? User::first();

        if (!$user) {
            $this->command?->error('No user found to assign stock images.');
            return;
        }

        $assetsDir = database_path('seeders/assets/client-stock-images');
        $samples = [
            [
                'file' => 'client-stock-image-1.png',
                'title' => 'Open Road Junction Under Cloudy Sky',
                'description' => 'Wide landscape stock photo of a paved road junction with trees, street lighting, and dramatic cloudy skies. Ideal for property, transport, and commercial projects.',
                'image_category' => 'real_estate',
                'tags' => ['road', 'junction', 'landscape', 'property', 'outdoor'],
                'promotion_tier' => 'featured',
            ],
            [
                'file' => 'client-stock-image-2.png',
                'title' => 'Paved Intersection with Street Lights',
                'description' => 'Outdoor stock image of a quiet paved intersection with street lights, greenery, and open sky. Suitable for real estate, classifieds, and editorial use.',
                'image_category' => 'real_estate',
                'tags' => ['intersection', 'street', 'parking', 'commercial', 'landscape'],
                'promotion_tier' => 'promoted',
            ],
            [
                'file' => 'client-stock-image-3.png',
                'title' => 'Industrial Yard Entrance with Fencing',
                'description' => 'Stock photograph of an industrial yard entrance with metal fencing, asphalt surfaces, and landscaped trees under a bright cloudy sky.',
                'image_category' => 'business',
                'tags' => ['industrial', 'fence', 'yard', 'business', 'commercial'],
                'promotion_tier' => 'standard',
            ],
        ];

        foreach ($samples as $sample) {
            $sourcePath = $assetsDir . DIRECTORY_SEPARATOR . $sample['file'];

            if (!File::exists($sourcePath)) {
                $this->command?->warn("Missing asset: {$sample['file']}");
                continue;
            }

            $slug = Str::slug($sample['title']);

            if (ImagesAdvert::where('slug', $slug)->exists()) {
                $this->command?->info("Skipping existing image: {$sample['title']}");
                continue;
            }

            $storagePath = 'images/client-stock/' . $sample['file'];
            Storage::disk('public')->put($storagePath, File::get($sourcePath));

            $imageInfo = @getimagesize($sourcePath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;
            $orientation = 'landscape';

            if ($width && $height) {
                if ($width < $height) {
                    $orientation = 'portrait';
                } elseif ($width === $height) {
                    $orientation = 'square';
                }
            }

            ImagesAdvert::create([
                'user_id' => $user->user_id,
                'title' => $sample['title'],
                'slug' => $slug,
                'description' => $sample['description'],
                'short_description' => Str::limit($sample['description'], 140),
                'main_image' => $storagePath,
                'images' => [$storagePath],
                'thumbnail' => $storagePath,
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
                'color_type' => 'color',
                'image_category' => $sample['image_category'],
                'tags' => $sample['tags'],
                'license_type' => 'royalty_free',
                'standard_price' => 9.99,
                'extended_price' => 29.99,
                'exclusive_price' => 199.99,
                'currency' => 'GBP',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $user->user_id,
                'contact_name' => trim(($user->first_name ?? 'WWA') . ' ' . ($user->last_name ?? 'Admin')),
                'contact_email' => $user->email,
                'has_model_release' => false,
                'has_property_release' => false,
                'views_count' => 0,
                'downloads_count' => 0,
                'saves_count' => 0,
                'rating' => 0,
                'rating_count' => 0,
                'promotion_tier' => $sample['promotion_tier'],
                'is_verified_creator' => true,
                'is_active' => true,
            ]);

            $this->command?->info("Imported: {$sample['title']}");
        }
    }
}
