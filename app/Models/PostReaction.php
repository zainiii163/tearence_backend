<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReaction extends Model
{
    use HasFactory;

    protected $table = 'post_reactions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'post_id',
        'user_id',
        'reaction_type',
    ];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id', 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('reaction_type', $type);
    }
}
