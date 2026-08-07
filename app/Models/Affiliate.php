<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'affiliate_links';

    /**
     * Resolve a public URL for the stored image path without breaking Filament
     * FileUpload (which needs the raw relative path in attributes).
     */
    public function getImageUrlAttribute($image)
    {
        // Prefer raw attribute so Filament edit forms keep a disk path, not a full URL.
        $raw = $this->attributes['image_url'] ?? $image;

        if ($raw === null || $raw === '') {
            return $raw;
        }

        // Already an absolute / root-relative URL
        if (is_string($raw) && (
            str_starts_with($raw, 'http://')
            || str_starts_with($raw, 'https://')
            || str_starts_with($raw, '/')
        )) {
            return $raw;
        }

        // During Filament admin requests, return the stored path so FileUpload can bind.
        if (request() && (request()->is('admin*') || request()->is('livewire/*'))) {
            return $raw;
        }

        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($raw, 'affiliates');
    }
}
