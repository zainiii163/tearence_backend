<?php

namespace Database\Seeders;

use App\Models\ImagesAdvert;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Import WhatsApp / docs stock photos into Images & Media (Clive client shots).
 */
class DocsWhatsAppImagesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'rizky@worldwideadverts.info')->first()
            ?? User::where('is_super_admin', true)->first()
            ?? User::first();

        if (!$user) {
            $this->command?->error('No user found to assign docs images.');
            return;
        }

        $assetsDir = database_path('seeders/assets/docs-whatsapp-images');

        $samples = [
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.02 AM.jpeg',
                'title' => 'Industrial Yard with Yellow Cable Coils',
                'description' => 'Outdoor stock photo of a secure industrial yard behind black mesh fencing, with bright yellow coiled utility piping, no-parking bollards, and an asphalt access road under an overcast sky.',
                'image_category' => 'business',
                'tags' => ['industrial', 'utility', 'yellow-cable', 'fencing', 'commercial', 'yard'],
                'promotion_tier' => 'featured',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.02 AM (1).jpeg',
                'title' => 'Commercial Storage Yard Stock Photo',
                'description' => 'Stock image of a commercial storage and utility yard with fencing, outdoor equipment, and open asphalt surfaces. Suitable for business, property, and industrial listings.',
                'image_category' => 'business',
                'tags' => ['storage', 'commercial', 'yard', 'industrial', 'outdoor'],
                'promotion_tier' => 'promoted',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM.jpeg',
                'title' => 'Warehouse Units 43–47 with Loading Bays',
                'description' => 'Modern multi-unit warehouse terrace with numbered blue roller shutters (units 43–47), delivery van, and red waste bins on a wide asphalt forecourt. Ideal for property and business stock libraries.',
                'image_category' => 'real_estate',
                'tags' => ['warehouse', 'units', 'loading-bay', 'property', 'commercial'],
                'promotion_tier' => 'featured',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (1).jpeg',
                'title' => 'Industrial Estate Forecourt',
                'description' => 'Wide view of an industrial estate forecourt and commercial buildings under soft daylight. Useful for real estate, logistics, and business marketing.',
                'image_category' => 'real_estate',
                'tags' => ['industrial-estate', 'forecourt', 'property', 'logistics'],
                'promotion_tier' => 'promoted',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (2).jpeg',
                'title' => 'Business Park Exterior Stock Shot',
                'description' => 'Exterior photograph of a business park / light industrial premises with parking area and contemporary cladding. Royalty-free commercial stock.',
                'image_category' => 'business',
                'tags' => ['business-park', 'exterior', 'parking', 'commercial'],
                'promotion_tier' => 'standard',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (3).jpeg',
                'title' => 'M61 Roadside with Pylon and Industrial Site',
                'description' => 'Roadside stock photo near the M61 with painted road markings, electricity pylon, digital speed sign, and a blue industrial building with crane under a clear blue sky.',
                'image_category' => 'travel',
                'tags' => ['motorway', 'M61', 'pylon', 'roadside', 'construction', 'travel'],
                'promotion_tier' => 'featured',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (4).jpeg',
                'title' => 'Road and Infrastructure Landscape',
                'description' => 'Outdoor landscape showing road infrastructure and nearby commercial development. Suitable for travel, transport, and property campaigns.',
                'image_category' => 'travel',
                'tags' => ['road', 'infrastructure', 'landscape', 'transport'],
                'promotion_tier' => 'standard',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (5).jpeg',
                'title' => 'Commercial Premises Stock Photo',
                'description' => 'Stock photograph of commercial premises and surrounding grounds. Ideal for classifieds, business directories, and property marketing.',
                'image_category' => 'real_estate',
                'tags' => ['premises', 'commercial', 'property', 'stock'],
                'promotion_tier' => 'standard',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (6).jpeg',
                'title' => 'Outdoor Industrial Site View',
                'description' => 'Outdoor view of an industrial site with fencing, vehicles, and commercial buildings. Royalty-free image for business and property use.',
                'image_category' => 'business',
                'tags' => ['industrial', 'site', 'outdoor', 'vehicles', 'business'],
                'promotion_tier' => 'promoted',
            ],
            [
                'file' => 'WhatsApp Image 2026-08-03 at 1.25.03 AM (7).jpeg',
                'title' => 'Warehouse and Yard Complex',
                'description' => 'Stock image of a warehouse and yard complex with open hardstanding. Suitable for logistics, real estate, and industrial advertising.',
                'image_category' => 'real_estate',
                'tags' => ['warehouse', 'yard', 'logistics', 'industrial', 'property'],
                'promotion_tier' => 'featured',
            ],
        ];

        foreach ($samples as $index => $sample) {
            $sourcePath = $assetsDir . DIRECTORY_SEPARATOR . $sample['file'];

            if (! File::exists($sourcePath)) {
                $this->command?->warn("Missing asset: {$sample['file']}");
                continue;
            }

            $slug = Str::slug($sample['title']);

            if (ImagesAdvert::where('slug', $slug)->exists()) {
                $this->command?->info("Skipping existing image: {$sample['title']}");
                continue;
            }

            $safeName = 'docs-whatsapp-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '.jpeg';
            $storagePath = 'images/client-stock/' . $safeName;
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
                'media_type' => 'image',
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
