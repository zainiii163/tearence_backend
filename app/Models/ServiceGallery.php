<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'image_path',
        'title',
        'description',
        'sort_order',
        'is_video',
        'video_url',
    ];

    protected $casts = [
        'is_video' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('is_video', false);
    }

    public function scopeVideos($query)
    {
        return $query->where('is_video', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
