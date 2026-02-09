<?php

namespace Database\Seeders;

use App\Models\CandidateProfile;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CandidateProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::take(3)->get();
        $locations = Location::take(3)->get();

        if ($customers->count() === 0) {
            $this->command->warn('No customers found. Skipping CandidateProfileSeeder.');
            return;
        }

        $profiles = [
            [
                'customer_id' => $customers[0]->customer_id,
                'headline' => 'Senior Software Engineer with 8+ Years Experience',
                'summary' => 'Experienced software engineer specializing in web development, cloud architecture, and team leadership.',
                'skills' => json_encode(['PHP', 'Laravel', 'JavaScript', 'React', 'AWS', 'Docker']),
                'cv_url' => 'cvs/john-doe-cv.pdf',
                'location_id' => $locations->count() > 0 ? $locations[0]->location_id : null,
                'visibility' => 'public',
                'is_featured' => true,
                'featured_expires_at' => now()->addDays(30),
            ],
            [
                'customer_id' => $customers->count() > 1 ? $customers[1]->customer_id : $customers[0]->customer_id,
                'headline' => 'Frontend Developer & UI/UX Designer',
                'summary' => 'Creative frontend developer with a passion for creating beautiful and functional user interfaces.',
                'skills' => json_encode(['HTML', 'CSS', 'JavaScript', 'Vue.js', 'Figma', 'Adobe XD']),
                'cv_url' => 'cvs/jane-smith-cv.pdf',
                'location_id' => $locations->count() > 1 ? $locations[1]->location_id : null,
                'visibility' => 'public',
                'is_featured' => false,
            ],
            [
                'customer_id' => $customers->count() > 2 ? $customers[2]->customer_id : $customers[0]->customer_id,
                'headline' => 'Full Stack Developer | Python & JavaScript',
                'summary' => 'Versatile full-stack developer with expertise in Python, Django, and modern JavaScript frameworks.',
                'skills' => json_encode(['Python', 'Django', 'JavaScript', 'Node.js', 'PostgreSQL', 'MongoDB']),
                'cv_url' => 'cvs/michael-johnson-cv.pdf',
                'location_id' => $locations->count() > 2 ? $locations[2]->location_id : null,
                'visibility' => 'public',
                'is_featured' => false,
                'has_job_alerts_boost' => true,
                'job_alerts_boost_expires_at' => now()->addDays(15),
            ],
        ];

        foreach ($profiles as $profile) {
            CandidateProfile::create($profile);
        }
    }
}

