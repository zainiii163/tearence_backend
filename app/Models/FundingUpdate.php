<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'funding_project_id',
        'title',
        'content',
        'images',
        'update_type',
        'is_public',
    ];

    protected $casts = [
        'images' => 'array',
        'is_public' => 'boolean',
    ];

    public static function getUpdateTypes(): array
    {
        return [
            'milestone' => 'Milestone',
            'progress' => 'Progress Update',
            'announcement' => 'Announcement',
            'thank_you' => 'Thank You',
        ];
    }

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('update_type', $type);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
