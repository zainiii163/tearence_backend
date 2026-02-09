<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'blog';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'blog_id';

    public function getImageAttribute($image)
    {
        $fileUpload = new FileUploadHelper();
        // Check if $image starts with "/uploads/images/blog"
        if (strpos($image, "/uploads/images/blog") !== false) {
            // Remove "/uploads/images/blog" from the image path
            $path = str_replace("/uploads/images/blog", "", $image);
        } else if (strpos($image, "blog/") !== false) {
            $path = str_replace("blog/", "", $image);
        } else {
            // If image already has "blog/" just use it
            $path = $image;
        }
        return $fileUpload->getFile($path, 'blog');
    }

    // Automatically generate slug from title
    public static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            $recipe->slug = Str::slug($recipe->title);
        });
    }
}
