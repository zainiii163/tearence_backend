<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'group';

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

    protected static function booted(): void
    {
        static::saving(function (Group $group) {
            if (!$group->type) {
                $group->type = $group->parent_id ? 'role' : 'team';
            }
            if ($group->type === 'team') {
                $group->parent_id = null;
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'group_id');
    }

    public function subRoles(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'group_id')->where('type', 'role');
    }

    public function scopeTeams($query)
    {
        return $query->where('type', 'team')->orWhere(function ($q) {
            $q->whereNull('parent_id')->where(function ($q2) {
                $q2->where('type', 'team')->orWhereNull('type');
            });
        });
    }

    public function scopeRoles($query)
    {
        return $query->where('type', 'role')->orWhereNotNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isTeam(): bool
    {
        return ($this->type === 'team') || ($this->parent_id === null && $this->type !== 'role');
    }

    public function isRole(): bool
    {
        return !$this->isTeam();
    }

    public function fullLabel(): string
    {
        if ($this->isTeam() || !$this->team) {
            return (string) $this->name;
        }
        return $this->team->name . ' / ' . $this->name;
    }

    /**
     * Options for Filament select: "Team / Role" => group_id (roles preferred; teams included if no roles).
     */
    public static function optionsGroupedByTeam(): array
    {
        $options = [];
        $teams = self::query()
            ->where(function ($q) {
                $q->where('type', 'team')->orWhere(function ($q2) {
                    $q2->whereNull('parent_id')->where(function ($q3) {
                        $q3->whereNull('type')->orWhere('type', 'team');
                    });
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->with(['subRoles' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->get();

        foreach ($teams as $team) {
            $roles = $team->subRoles;
            if ($roles->isEmpty()) {
                $options[$team->name] = [$team->group_id => $team->name . ' (team)'];
                continue;
            }
            $options[$team->name] = $roles->mapWithKeys(
                fn (Group $role) => [$role->group_id => $role->name]
            )->all();
        }

        // Orphan roles (parent missing)
        $orphans = self::query()
            ->where('type', 'role')
            ->whereNotNull('parent_id')
            ->whereDoesntHave('team')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        if ($orphans->isNotEmpty()) {
            $options['Other roles'] = $orphans->mapWithKeys(
                fn (Group $role) => [$role->group_id => $role->name]
            )->all();
        }

        return $options;
    }
}
