<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMarketingAsset extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'project_id',
        'pitch_video_url',
        'documents',
    ];

    protected $casts = [
        'documents' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Helper methods
    public function hasPitchVideo()
    {
        return !empty($this->pitch_video_url);
    }

    public function getVideoEmbedUrl()
    {
        if (!$this->pitch_video_url) {
            return null;
        }

        // Convert YouTube URL to embed URL
        if (str_contains($this->pitch_video_url, 'youtube.com/watch?v=')) {
            $videoId = explode('v=', $this->pitch_video_url)[1];
            $videoId = explode('&', $videoId)[0]; // Remove additional parameters
            return "https://www.youtube.com/embed/" . $videoId;
        }

        if (str_contains($this->pitch_video_url, 'youtu.be/')) {
            $videoId = explode('youtu.be/', $this->pitch_video_url)[1];
            $videoId = explode('?', $videoId)[0]; // Remove additional parameters
            return "https://www.youtube.com/embed/" . $videoId;
        }

        // Return original URL if not YouTube
        return $this->pitch_video_url;
    }

    public function getDocumentCount()
    {
        return count($this->documents ?? []);
    }

    public function getDocumentsByType($type)
    {
        return collect($this->documents ?? [])->filter(function ($doc) use ($type) {
            return ($doc['type'] ?? null) === $type;
        });
    }
}
