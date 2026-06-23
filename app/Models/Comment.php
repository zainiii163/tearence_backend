<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comments';
    protected $primaryKey = 'comment_id';
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'comment_id',
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'comment_type',
        'reactions_count',
        'replies_count',
        'is_flagged',
        'flag_reason',
        'is_hidden',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id', 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'comment_id');
    }

    public function reactions()
    {
        return $this->hasMany(CommentReaction::class, 'comment_id', 'comment_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('comment_type', $type);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeNotFlagged($query)
    {
        return $query->where('is_flagged', false);
    }

    public function scopeNotHidden($query)
    {
        return $query->where('is_hidden', false);
    }

    public function incrementReactions()
    {
        $this->increment('reactions_count');
    }

    public function decrementReactions()
    {
        $this->decrement('reactions_count');
    }

    public function incrementReplies()
    {
        $this->increment('replies_count');
    }

    public function decrementReplies()
    {
        $this->decrement('replies_count');
    }
}
