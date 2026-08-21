<?php

namespace App\Console\Commands;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Customer;
use App\Models\CustomerBusiness;
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
 */
class SetupMgnitBusinessCommand extends Command
{
    protected $signature = 'business:setup-mgnit
                            {--password=Madamombe : Login password for mgnit3377@gmail.com}
                            {--ensure-social : Also create/link Social Hub page}';

    protected $description = 'Create/update MGNIT LTD account, business profile, logo and cover images';

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

        $description = 'Madamombe Global Network IT Limited (MGNIT LTD) is a UK IT company specialising in web development, mobile apps, software, graphics and digital solutions. Company no 08562768.';

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
        $profile['booking_url'] = 'https://mgnit.co.uk';
        $profile['services'] = [
            'Web development',
            'Mobile app development',
            'Software development',
            'Logo & graphics design',
            'Web hosting & domains',
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

        // Extra gallery images
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
        $business->category_profile = $profile;
        $business->save();

        $this->info('Business saved: id='.$business->id.' slug='.$business->slug);
        $this->line('Public page: https://worldwideadverts.info/business/'.$business->id);

        if ($this->option('ensure-social') && Schema::hasColumn('communities', 'business_id')) {
            $community = Community::where('business_id', $business->id)->first();
            if (! $community) {
                $baseName = 'MGNIT LTD — updates';
                $slug = Str::slug($baseName);
                $original = $slug;
                $n = 1;
                while (Community::where('slug', $slug)->exists()) {
                    $slug = $original.'-'.$n++;
                }

                $creatorUserId = \App\Models\User::where('email', $email)->value('user_id');

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

        $this->newLine();
        $this->info('Done. Login: '.$email.' / '.$password);

        return self::SUCCESS;
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
