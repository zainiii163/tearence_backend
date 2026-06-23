<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityFollow extends Model
{
    use HasFactory;

    protected $table = 'community_follows';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'community_id',
        'followed_at',
    ];

    protected $casts = [
        'followed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id', 'community_id');
    }
}
