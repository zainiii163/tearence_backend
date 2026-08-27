<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class FundingProjectSchema
{
    /**
     * Map Filament form names onto the columns that actually exist on funding_projects.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeAdminPayload(array $data): array
    {
        $aliases = [
            'problem_solving' => 'problem_solved',
            'why_now' => 'why_matters_now',
            'pitch_video' => 'pitch_video_url',
            'documents' => 'verification_documents',
            'city' => 'region',
            'funding_starts_at' => 'published_at',
            'funding_ends_at' => 'funding_deadline',
            'amount_raised' => 'current_funded',
            'backer_count' => 'backers_count',
        ];

        foreach ($aliases as $from => $to) {
            if (! array_key_exists($from, $data)) {
                continue;
            }
            if (! array_key_exists($to, $data) || $data[$to] === null || $data[$to] === '') {
                $data[$to] = $data[$from];
            }
        }

        $filtered = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn('funding_projects', $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
