<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardPermission extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     */
    protected $table = 'dashboard_permissions';
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'permission_id';

    protected $casts = [
        'filters' => 'array',
        'can_view' => 'boolean',
        'can_export' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the group that owns the permission.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    /**
     * Get the user that owns the permission.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Check if a user has permission to view a dashboard section.
     */
    public static function userCanView($userId, $section)
    {
        // Check user-specific permissions first
        $userPermission = self::where('user_id', $userId)
            ->where('dashboard_section', $section)
            ->where('can_view', true)
            ->first();

        if ($userPermission) {
            return true;
        }

        // Check group permissions
        $user = User::find($userId);
        if ($user && $user->group_id) {
            $groupPermission = self::where('group_id', $user->group_id)
                ->where('dashboard_section', $section)
                ->where('can_view', true)
                ->first();

            if ($groupPermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user can export from a dashboard section.
     */
    public static function userCanExport($userId, $section)
    {
        // Check user-specific permissions first
        $userPermission = self::where('user_id', $userId)
            ->where('dashboard_section', $section)
            ->where('can_export', true)
            ->first();

        if ($userPermission) {
            return true;
        }

        // Check group permissions
        $user = User::find($userId);
        if ($user && $user->group_id) {
            $groupPermission = self::where('group_id', $user->group_id)
                ->where('dashboard_section', $section)
                ->where('can_export', true)
                ->first();

            if ($groupPermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get available filters for a user in a dashboard section.
     */
    public static function getUserFilters($userId, $section)
    {
        // Check user-specific filters first
        $userPermission = self::where('user_id', $userId)
            ->where('dashboard_section', $section)
            ->first();

        if ($userPermission && $userPermission->filters) {
            return $userPermission->filters;
        }

        // Check group filters
        $user = User::find($userId);
        if ($user && $user->group_id) {
            $groupPermission = self::where('group_id', $user->group_id)
                ->where('dashboard_section', $section)
                ->first();

            if ($groupPermission && $groupPermission->filters) {
                return $groupPermission->filters;
            }
        }

        return [];
    }

    /**
     * Get all dashboard sections a user can access.
     */
    public static function getUserAccessibleSections($userId)
    {
        $sections = [];
        
        // Get user-specific permissions
        $userPermissions = self::where('user_id', $userId)
            ->where('can_view', true)
            ->pluck('dashboard_section')
            ->toArray();

        // Get group permissions
        $user = User::find($userId);
        if ($user && $user->group_id) {
            $groupPermissions = self::where('group_id', $user->group_id)
                ->where('can_view', true)
                ->pluck('dashboard_section')
                ->toArray();

            $sections = array_merge($sections, $groupPermissions);
        }

        $sections = array_merge($sections, $userPermissions);
        
        return array_unique($sections);
    }

    /**
     * Set permissions for a group.
     */
    public static function setGroupPermissions($groupId, $section, $canView = false, $canExport = false, $filters = [])
    {
        return self::updateOrCreate(
            [
                'group_id' => $groupId,
                'dashboard_section' => $section,
                'user_id' => null,
            ],
            [
                'can_view' => $canView,
                'can_export' => $canExport,
                'filters' => $filters,
            ]
        );
    }

    /**
     * Set permissions for a user.
     */
    public static function setUserPermissions($userId, $section, $canView = false, $canExport = false, $filters = [])
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'dashboard_section' => $section,
                'group_id' => null,
            ],
            [
                'can_view' => $canView,
                'can_export' => $canExport,
                'filters' => $filters,
            ]
        );
    }
}
