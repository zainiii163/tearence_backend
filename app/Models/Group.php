<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'group_id';

    protected $casts = [
        'permissions' => 'array',
        'can_manage_users' => 'boolean',
        'can_manage_categories' => 'boolean',
        'can_manage_listings' => 'boolean',
        'can_manage_dashboard' => 'boolean',
        'can_view_analytics' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the group.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }
}
