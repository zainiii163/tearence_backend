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

    public function getImageUrlAttribute($image)
    {
        $fileUpload = new FileUploadHelper();
        return $fileUpload->getFile($image, 'affiliates');
    }
}
