<?php

namespace App\Models;

use App\Helpers\FileUploadHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerStore extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_store';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'store_id';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function getStoreBannerAttribute($image)
    {
        $fileUpload = new FileUploadHelper();
        $path = str_replace("/uploads/images/store", "", $image);
        return $fileUpload->getFile($path, 'store');
    }

    public function getStoreLogoAttribute($image)
    {
        $fileUpload = new FileUploadHelper();
        $path = str_replace("/uploads/images/store", "", $image);
        return $fileUpload->getFile($path, 'store');
    }
}
