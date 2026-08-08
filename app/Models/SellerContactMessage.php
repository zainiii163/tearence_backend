<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerContactMessage extends Model
{
    protected $fillable = [
        'hub',
        'listing_id',
        'seller_user_id',
        'buyer_user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'contact_method',
        'message',
        'status',
    ];
}
