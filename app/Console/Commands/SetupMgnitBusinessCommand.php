<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\Job;
use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use App\Models\User;
use App\Support\JobSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-shot setup for Clive's MGNIT LTD test business on WWA.
 *
 * php artisan business:setup-mgnit
 * php artisan business:setup-mgnit --ensure-social
 */
class SetupMgnitBusinessCommand extends Command
{
    protected $signature = 'business:setup-mgnit
                            {--password=Madamombe : Login password for mgnit3377@gmail.com}
                            {--ensure-social : Also create/link Social Hub page}';

    protected $description = 'Create/update MGNIT LTD account, professional profile, careers and promotions';

    public function handle(): int
    {
        $email = 'mgnit3377@gmail.com';
        $password = (string) $this->option('password');

        $this->info('Setting up MGNIT LTD…');

        $customer = Customer::where('email', $email)->first();
        if (! $customer) {
            $customer = new Customer();
            $customer->customer_uid = Str::random(10);
            $customer->first_name = 'MGNIT';
            $customer->last_name = 'LTD';
            $customer->email = $email;
            $customer->affiliate_id = '';
            $customer->affiliated_members = 0;
            if (Schema::hasColumn('customer', 'user_type')) {
                $customer->user_type = 'business';
            }
            if (Schema::hasColumn('customer', 'email_verified_at')) {
                $customer->email_verified_at = now();
            }
            $this->info('Created customer account.');
        } else {
            $this->info('Customer already exists (id '.$customer->customer_id.').');
        }

        $customer->password_hash = Hash::make($password);
        if (Schema::hasColumn('customer', 'first_name') && ! $customer->first_name) {
            $customer->first_name = 'MGNIT';
        }
        if (Schema::hasColumn('customer', 'last_name') && ! $customer->last_name) {
            $customer->last_name = 'LTD';
        }
        $customer->save();
        $this->info('Password set for '.$email);

        $business = CustomerBusiness::where('customer_id', $customer->customer_id)
            ->where('business_name', 'like', '%MGNIT%')
            ->orderByDesc('id')
            ->first();

        if (! $business) {
            $business = CustomerBusiness::where('id', 39)->first();
        }

        if (! $business) {
            $business = new CustomerBusiness();
            $business->customer_id = $customer->customer_id;
            $business->slug = 'mgnit-ltd';
            $business->status = 'active';
            $this->info('Creating new business row…');
        } else {
            $business->customer_id = $customer->customer_id;
            $this->info('Updating business id '.$business->id);
        }

        $description = "Madamombe Global Network IT Limited (MGNIT LTD) is a UK technology company delivering web design, web development, mobile apps, software engineering, logo & brand design, and AI solutions for businesses worldwide.\n\nCompany no. 08562768 · VAT 834575499435 · D-U-N-S 219466057\n\nWe help organisations launch professional digital products — from marketing sites and design themes to custom software and intelligent automation.";

        $business->business_name = 'MGNIT LTD';
        $business->business_company_name = 'MGNIT LTD';
        $business->business_company_no = '08562768';
        $business->business_company_registration = '08562768';
        $business->vat_number = '834575499435';
        $business->duns_number = '219466057';
        $business->business_website = 'https://mgnit.co.uk';
        $business->business_email = 'info@mgnit.co.uk';
        $business->personal_email = $email;
        $business->business_owner = 'MGNIT LTD';
        $business->business_description = $description;
        $business->business_address = "Kington Office\n61 Bridge Street\nKington\nHR5 3DJ\nHerefordshire";
        $business->city = 'Kington';
        $business->country = 'United Kingdom';
        $business->postal_code = 'HR5 3DJ';
        $business->business_phone_number = '+44 20 0000 0000';
        $business->business_category_slug = 'technology-electronics';
        $business->booking_url = 'https://mgnit.co.uk';
        $business->status = 'active';
        if (Schema::hasColumn('customer_business', 'slug') && ! $business->slug) {
            $business->slug = 'mgnit-ltd';
        }

        $services = [
            'Web design & development',
            'Mobile app development',
            'Software engineering',
            'Logo & brand graphics',
            'UI / design themes',
            'AI & automation solutions',
            'Web hosting & domains',
        ];

        $profile = is_array($business->category_profile) ? $business->category_profile : [];
        $profile['opening_hours'] = [
            'monday' => '09:00 – 18:00',
            'tuesday' => '09:00 – 18:00',
            'wednesday' => '09:00 – 18:00',
            'thursday' => '09:00 – 18:00',
            'friday' => '09:00 – 18:00',
            'saturday' => '10:00 – 16:00',
            'sunday' => 'Closed',
        ];
        $profile['support_hours'] = $profile['opening_hours'];
        $profile['booking_url'] = 'https://mgnit.co.uk';
        $profile['services'] = $services;
        $profile['products'] = $services;
        $profile['highlights'] = [
            'UK-registered technology company',
            'Web, apps, software & AI',
            'Design themes for marketing sites',
            'Consultation bookings available',
        ];
        $profile['gallery'] = [];
        $business->category_profile = $profile;

        // Logo + cover from official site
        $logoPath = $this->storeRemoteImage(
            'https://www.mgnit.co.uk/wp-content/uploads/2025/12/0-removebg-preview.png',
            'business',
            'mgnit-logo'
        );
        if (! $logoPath) {
            $logoPath = $this->storeRemoteImage(
                'https://www.mgnit.co.uk/wp-content/uploads/2020/09/0.png',
                'business',
                'mgnit-logo'
            );
        }
        if ($logoPath) {
            $business->business_logo = $logoPath;
            $this->info('Logo stored: '.$logoPath);
        } else {
            $this->warn('Could not download logo.');
        }

        $coverPath = $this->storeRemoteImage(
            'https://www.mgnit.co.uk/wp-content/uploads/2026/08/modified_image-14-scaled.png',
            'business',
            'mgnit-cover'
        );
        if ($coverPath) {
            $business->cover_image = $coverPath;
            $profile['gallery'][] = $coverPath;
            $this->info('Cover stored: '.$coverPath);
        }

        $galleryUrls = [
            'https://www.mgnit.co.uk/wp-content/uploads/2026/08/modified_image-15-scaled.png',
            'https://www.mgnit.co.uk/wp-content/uploads/2026/08/download-6.png',
            'https://www.mgnit.co.uk/wp-content/uploads/2026/08/download-7.png',
        ];
        foreach ($galleryUrls as $i => $url) {
            $path = $this->storeRemoteImage($url, 'business', 'mgnit-gallery-'.$i);
            if ($path) {
                $profile['gallery'][] = $path;
            }
        }

        $careers = $this->seedCareers($customer);
        $profile['careers'] = $careers;
        $business->category_profile = $profile;
        $business->save();

        $this->seedPromotions($customer, $business);

        $this->info('Business saved: id='.$business->id.' slug='.($business->slug ?: 'mgnit-ltd'));
        $this->line('Public page: https://worldwideadverts.info/business/'.($business->slug ?: $business->id));

        if ($this->option('ensure-social') && Schema::hasColumn('communities', 'business_id')) {
            $this->ensureSocial($email, $business);
        }

        $this->newLine();
        $this->info('Done. Login: '.$email.' / '.$password);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function seedCareers(Customer $customer): array
    {
        if (! Schema::hasTable('jobs')) {
            $this->warn('jobs table missing — careers stored on profile only.');

            return $this->careerDefinitions();
        }

        $seeded = [];
        foreach ($this->careerDefinitions() as $role) {
            $payload = JobSchema::filterPayload([
                'user_id' => $customer->customer_id,
                'title' => $role['title'],
                'slug' => Str::slug('mgnit-'.$role['title']).'-'.$customer->customer_id,
                'description' => $role['description'],
                'company_name' => 'MGNIT LTD',
                'company_website' => 'https://mgnit.co.uk',
                'company_industry' => 'Information Technology',
                'contact_email' => 'info@mgnit.co.uk',
                'application_email' => 'info@mgnit.co.uk',
                'application_link' => $role['apply_url'],
                'country' => 'United Kingdom',
                'city' => 'Kington',
                'location_name' => 'Kington / Remote (UK)',
                'work_type' => $role['work_type'],
                'experience_level' => $role['experience_level'],
                'is_remote' => true,
                'remote_available' => true,
                'is_active' => true,
                'status' => 'active',
                'is_verified_employer' => true,
                'verified_employer' => true,
                'salary_currency' => 'GBP',
                'currency' => 'GBP',
                'posted_at' => now(),
                'expires_at' => now()->addMonths(3),
            ]);

            $existing = Job::where('user_id', $customer->customer_id)
                ->where('title', $role['title'])
                ->first();

            if ($existing) {
                $existing->fill($payload);
                $existing->save();
                $job = $existing;
            } else {
                $job = Job::create($payload);
            }

            $seeded[] = [
                'id' => $job->id,
                'title' => $role['title'],
                'description' => $role['description'],
                'location' => 'Kington / Remote (UK)',
                'type' => $role['work_type'],
                'work_type' => $role['work_type'],
                'apply_url' => $role['apply_url'],
                'application_link' => $role['apply_url'],
            ];
        }

        $this->info('Careers seeded: '.count($seeded).' roles');

        return $seeded;
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function careerDefinitions(): array
    {
        $apply = 'https://mgnit.co.uk/careers';

        return [
            [
                'title' => 'Web Developer',
                'description' => 'Build and maintain responsive websites and web applications for MGNIT LTD clients using modern front-end and back-end stacks.',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'apply_url' => $apply,
            ],
            [
                'title' => 'App Developer',
                'description' => 'Design and ship iOS/Android (or cross-platform) apps for client projects, from MVP through production release.',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'apply_url' => $apply,
            ],
            [
                'title' => 'Logo Designer',
                'description' => 'Create professional logos, brand marks and visual identity systems for businesses using MGNIT design services.',
                'work_type' => 'contract',
                'experience_level' => 'mid',
                'apply_url' => $apply,
            ],
            [
                'title' => 'Software Engineer',
                'description' => 'Engineer reliable software products and integrations — APIs, dashboards and internal tools — for MGNIT LTD delivery teams.',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'apply_url' => $apply,
            ],
            [
                'title' => 'AI Engineer',
                'description' => 'Prototype and productionise AI features — assistants, automation and data workflows — for client and internal products.',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'apply_url' => $apply,
            ],
        ];
    }

    protected function seedPromotions(Customer $customer, CustomerBusiness $business): void
    {
        if (! Schema::hasTable('promoted_advert_categories') || ! Schema::hasTable('promoted_adverts')) {
            $this->warn('Promoted adverts tables missing — skipped promotions seed.');

            return;
        }

        $webDesign = PromotedAdvertCategory::firstOrCreate(
            ['slug' => 'web-design'],
            [
                'name' => 'Web Design',
                'description' => '',
                'icon' => 'heroicon-o-computer-desktop',
                'color' => '#2563EB',
                'is_active' => true,
                'sort_order' => 11,
            ]
        );

        $designThemes = PromotedAdvertCategory::firstOrCreate(
            ['slug' => 'design-themes'],
            [
                'name' => 'Design Themes',
                'description' => '',
                'icon' => 'heroicon-o-swatch',
                'color' => '#7C3AED',
                'is_active' => true,
                'sort_order' => 12,
            ]
        );

        // Clear empty category blurbs if columns exist
        foreach ([$webDesign, $designThemes] as $cat) {
            if (Schema::hasColumn('promoted_advert_categories', 'description')) {
                $cat->description = '';
                $cat->is_active = true;
                $cat->save();
            }
        }

        $userId = null;
        if (Schema::hasColumn('promoted_adverts', 'user_id')) {
            $user = User::where('email', $customer->email)->first();
            $userId = $user?->id ?? $user?->user_id ?? null;
        }

        $cover = $business->cover_image ?: $business->business_logo;

        $adverts = [
            [
                'title' => 'Professional Web Design by MGNIT LTD',
                'tagline' => 'Business websites that convert',
                'description' => 'Custom web design and development for companies that need a professional online presence — landing pages, corporate sites and marketing builds.',
                'category_id' => $webDesign->id,
                'advert_type' => 'service',
                'key_features' => [
                    'Responsive business websites',
                    'Brand-aligned layouts',
                    'SEO-ready structure',
                    'Hosting & domain support',
                ],
            ],
            [
                'title' => 'Design Themes for Promotions',
                'tagline' => 'Ready-to-launch visual themes',
                'description' => 'Modern design themes for promotional campaigns, product launches and business marketing pages — tailored by MGNIT LTD.',
                'category_id' => $designThemes->id,
                'advert_type' => 'service',
                'key_features' => [
                    'Campaign-ready themes',
                    'Colour & typography systems',
                    'Landing page kits',
                    'Editable for your brand',
                ],
            ],
            [
                'title' => 'Logo & Brand Graphics',
                'tagline' => 'Identity design for growing brands',
                'description' => 'Logo design, brand packs and graphics for businesses promoting on Worldwide Adverts and beyond.',
                'category_id' => $webDesign->id,
                'advert_type' => 'service',
                'key_features' => [
                    'Logo concepts',
                    'Brand colour systems',
                    'Social & advert creatives',
                ],
            ],
        ];

        foreach ($adverts as $item) {
            $slug = Str::slug($item['title']);
            $payload = [
                'title' => $item['title'],
                'slug' => $slug,
                'tagline' => $item['tagline'],
                'description' => $item['description'],
                'key_features' => $item['key_features'],
                'advert_type' => $item['advert_type'],
                'category_id' => $item['category_id'],
                'country' => 'United Kingdom',
                'city' => 'Kington',
                'price' => 0,
                'currency' => 'GBP',
                'price_type' => 'contact',
                'condition' => 'new',
                'main_image' => $cover,
                'seller_name' => 'MGNIT LTD',
                'business_name' => 'MGNIT LTD',
                'phone' => $business->business_phone_number,
                'email' => 'info@mgnit.co.uk',
                'website' => 'https://mgnit.co.uk',
                'logo' => $business->business_logo,
                'verified_seller' => true,
                'promotion_tier' => 'promoted_plus',
                'promotion_price' => 0,
                'promotion_start' => now()->subDay(),
                'promotion_end' => now()->addMonths(2),
                'status' => 'active',
                'is_active' => true,
                'is_featured' => true,
                'approved_at' => now(),
            ];

            if ($userId !== null) {
                $payload['user_id'] = $userId;
            }

            $filtered = [];
            foreach ($payload as $key => $value) {
                if (Schema::hasColumn('promoted_adverts', $key)) {
                    $filtered[$key] = $value;
                }
            }

            $existing = PromotedAdvert::where(function ($q) use ($slug, $item) {
                $q->where('slug', $slug)
                    ->orWhere(function ($inner) use ($item) {
                        $inner->where('business_name', 'MGNIT LTD')
                            ->where('title', $item['title']);
                    });
            })->first();

            if ($existing) {
                $existing->fill($filtered);
                $existing->save();
            } else {
                PromotedAdvert::create($filtered);
            }
        }

        $this->info('Promotions seeded: Web Design + Design Themes categories and MGNIT adverts');
    }

    protected function ensureSocial(string $email, CustomerBusiness $business): void
    {
        $community = Community::where('business_id', $business->id)->first();
        if (! $community) {
            $baseName = 'MGNIT LTD — updates';
            $slug = Str::slug($baseName);
            $original = $slug;
            $n = 1;
            while (Community::where('slug', $slug)->exists()) {
                $slug = $original.'-'.$n++;
            }

            $creatorUserId = User::where('email', $email)->value('user_id');

            $community = Community::create([
                'community_id' => (string) Str::uuid(),
                'name' => $baseName,
                'slug' => $slug,
                'description' => 'Follow MGNIT LTD for promotions, project updates and company news.',
                'cover_image' => $business->cover_image ?: $business->business_logo,
                'scope' => 'global',
                'city' => 'Kington',
                'created_by' => $creatorUserId,
                'business_id' => $business->id,
                'members_count' => $creatorUserId ? 1 : 0,
                'beginner_friendly' => true,
                'rules' => ['Be respectful', 'No spam', 'Share updates about MGNIT LTD only'],
            ]);

            if ($creatorUserId) {
                try {
                    CommunityMember::firstOrCreate(
                        [
                            'community_id' => $community->community_id,
                            'user_id' => $creatorUserId,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'role' => 'admin',
                            'joined_at' => now(),
                        ]
                    );
                } catch (\Throwable $e) {
                    $this->warn('Community member link skipped: '.$e->getMessage());
                }
            }

            $this->info('Social Hub created: /community/'.$community->slug);
        } else {
            $this->info('Social Hub already linked: /community/'.($community->slug ?: $community->community_id));
        }
    }

    protected function storeRemoteImage(string $url, string $folder, string $prefix): ?string
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'User-Agent' => 'WWA-MGNIT-Setup/1.0',
            ])->get($url);

            if (! $response->successful()) {
                return null;
            }

            $bytes = $response->body();
            if ($bytes === '' || strlen($bytes) < 100) {
                return null;
            }

            $ext = 'png';
            $contentType = strtolower((string) $response->header('Content-Type'));
            if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
                $ext = 'jpg';
            } elseif (str_contains($contentType, 'webp')) {
                $ext = 'webp';
            } elseif (str_contains($url, '.jpg') || str_contains($url, '.jpeg')) {
                $ext = 'jpg';
            }

            $fileName = $prefix.'-'.Str::lower(Str::random(6)).'.'.$ext;
            $diskExists = array_key_exists($folder, config('filesystems.disks', []));

            if ($diskExists) {
                Storage::disk($folder)->put($fileName, $bytes);

                return $fileName;
            }

            $path = trim($folder, '/').'/'.$fileName;
            Storage::disk('public')->put($path, $bytes);

            return $path;
        } catch (\Throwable $e) {
            $this->warn('Image download failed ('.$url.'): '.$e->getMessage());

            return null;
        }
    }
}
