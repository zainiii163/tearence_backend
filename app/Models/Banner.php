<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banner';

    public function getImgAttribute($image)
    {
        $fileUpload = new FileUploadHelper();

        // Check if $image starts with "/uploads/images/banner"
        if (strpos($image, '/uploads/images/banner') !== false) {
            // Remove "/uploads/images/banner" from the image path
            $path = str_replace("/uploads/images/banner", "", $image);
        } else {
            // If image already has "banner/" just use it
            $path = $image;
        }

        // Return the processed file path using the FileUploadHelper
        return $fileUpload->getFile($path, 'banner');
    }

    public function setImgAttribute($value)
    {
        $this->attributes['img'] = basename($value); // Only store the file name
    }

    /**
     * Get the user that created the banner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
