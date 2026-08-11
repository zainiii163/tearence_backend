<?php

namespace Database\Seeders;

use App\Models\BusinessTool;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class BusinessToolSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('business_tools')) {
            $this->command?->warn('business_tools table missing — run migrations first.');

            return;
        }

        $tools = [
            [
                'slug' => 'ad-campaign-kit',
                'title' => 'Ad campaign kit',
                'blurb' => 'Brief, creative checklist, budget sheet and KPI tracker for sponsored / featured / promoted ads.',
                'description' => 'Full advertising campaign pack for Worldwide Adverts businesses.',
                'tag' => 'Advertising',
                'price' => 29,
                'price_label' => 'From $29',
                'icon' => 'volume',
                'file_url' => '/templates/commercial-agreement.html',
                'preview_url' => '/business/templates',
                'sort_order' => 10,
            ],
            [
                'slug' => 'social-content-calendar',
                'title' => 'Social content calendar',
                'blurb' => '30-day planner for posts, Reels and affiliate hops — fillable month grid.',
                'tag' => 'Marketing',
                'price' => 19,
                'price_label' => 'From $19',
                'icon' => 'share',
                'file_url' => '/templates/monthly-calendar-planner.html',
                'sort_order' => 20,
            ],
            [
                'slug' => 'email-promo-pack',
                'title' => 'Email promo pack',
                'blurb' => 'Launch, nurture and win-back email outlines for WWA listings and offers.',
                'tag' => 'Marketing',
                'price' => 22,
                'price_label' => 'From $22',
                'icon' => 'mail',
                'file_url' => '/business/templates',
                'sort_order' => 30,
            ],
            [
                'slug' => 'affiliate-creative-pack',
                'title' => 'Affiliate creative pack',
                'blurb' => 'Hooks, CTAs and creative prompts influencers can use when promoting your offer.',
                'tag' => 'Affiliates',
                'price' => 24,
                'price_label' => 'From $24',
                'icon' => 'target',
                'file_url' => '/affiliates?postForm=true&mode=business',
                'sort_order' => 40,
            ],
            [
                'slug' => 'seo-listing-booster',
                'title' => 'SEO listing booster',
                'blurb' => 'Title formulas, keyword checklist and schema tips for marketplace listings.',
                'tag' => 'Growth',
                'price' => 18,
                'price_label' => 'From $18',
                'icon' => 'chart',
                'file_url' => '/business/templates',
                'sort_order' => 50,
            ],
            [
                'slug' => 'commercial-agreement-tool',
                'title' => 'Commercial agreement (dispute-ready)',
                'blurb' => 'B2B contract with dispute / ADR clauses — fillable HTML.',
                'tag' => 'Legal',
                'price' => 24,
                'price_label' => 'From $24',
                'icon' => 'tool',
                'file_url' => '/templates/commercial-agreement.html',
                'sort_order' => 60,
            ],
            [
                'slug' => 'listing-boost-pack',
                'title' => 'Listing boost pack',
                'blurb' => 'Featured / promoted / sponsored checklist plus copy formulas for faster enquiries.',
                'tag' => 'Advertising',
                'category_slug' => 'adverts',
                'price' => 35,
                'price_label' => 'From $35',
                'icon' => 'volume',
                'sort_order' => 70,
            ],
            [
                'slug' => 'vehicle-listing-pack',
                'title' => 'Vehicle listing pack',
                'blurb' => 'Stock sheet, bill of sale outline and enquiry follow-up scripts for vehicle dealers.',
                'tag' => 'Marketing',
                'category_slug' => 'vehicles',
                'price' => 27,
                'price_label' => 'From $27',
                'icon' => 'tool',
                'sort_order' => 80,
            ],
            [
                'slug' => 'property-rental-pack',
                'title' => 'Property rental pack',
                'blurb' => 'Lease proposal outline, viewing checklist and tenant enquiry scripts.',
                'tag' => 'Marketing',
                'category_slug' => 'property',
                'price' => 32,
                'price_label' => 'From $32',
                'icon' => 'tool',
                'sort_order' => 90,
            ],
        ];

        foreach ($tools as $row) {
            BusinessTool::updateOrCreate(
                ['slug' => $row['slug']],
                array_merge([
                    'status' => 'active',
                    'currency' => 'USD',
                    'description' => $row['blurb'] ?? null,
                ], $row)
            );
        }
    }
}
