<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPollVote extends Model
{
    use HasFactory;

    protected $table = 'community_poll_votes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'post_id',
        'user_id',
        'option_id',
    ];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id', 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
