<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFundingDetail extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'project_id',
        'use_of_funds',
        'milestones',
        'funding_breakdown',
    ];

    protected $casts = [
        'use_of_funds' => 'array',
        'milestones' => 'array',
        'funding_breakdown' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Helper methods for working with milestones
    public function getCompletedMilestones()
    {
        return collect($this->milestones ?? [])->filter(function ($milestone) {
            return $milestone['completed'] ?? false;
        });
    }

    public function getPendingMilestones()
    {
        return collect($this->milestones ?? [])->filter(function ($milestone) {
            return !($milestone['completed'] ?? false);
        });
    }

    public function getTotalMilestoneAmount()
    {
        return collect($this->milestones ?? [])->sum('amount');
    }

    public function getCompletedMilestoneAmount()
    {
        return $this->getCompletedMilestones()->sum('amount');
    }
}
