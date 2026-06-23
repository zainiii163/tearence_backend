<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPost extends Model
{
    use HasFactory;

    protected $table = 'saved_posts';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'post_id',
        'collection_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id', 'post_id');
    }

    public function scopeByCollection($query, $collectionName)
    {
        return $query->where('collection_name', $collectionName);
    }
}
