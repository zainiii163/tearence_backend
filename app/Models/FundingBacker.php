<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundingBacker extends Model
{
    use HasFactory;

    protected $fillable = [
        'funding_project_id',
        'customer_id',
        'amount',
        'status',
        'is_anonymous',
        'funding_reward_id',
        'message',
        'backed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'backed_at' => 'datetime',
    ];

    public function fundingProject(): BelongsTo
    {
        return $this->belongsTo(FundingProject::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function fundingReward(): BelongsTo
    {
        return $this->belongsTo(FundingReward::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopePublic($query)
    {
        return $query->where('is_anonymous', false);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($backer) {
            if (empty($backer->backed_at)) {
                $backer->backed_at = now();
            }
        });

        static::created(function ($backer) {
            // Update project funding stats
            $project = $backer->fundingProject;
            if ($backer->status === 'completed') {
                $project->increment('current_funded', $backer->amount);
                $project->increment('backers_count');
            }
        });

        static::updated(function ($backer) {
            // Handle status changes
            if ($backer->wasChanged('status')) {
                $project = $backer->fundingProject;
                $oldStatus = $backer->getOriginal('status');
                $newStatus = $backer->status;

                if ($oldStatus !== 'completed' && $newStatus === 'completed') {
                    $project->increment('current_funded', $backer->amount);
                    $project->increment('backers_count');
                } elseif ($oldStatus === 'completed' && $newStatus !== 'completed') {
                    $project->decrement('current_funded', $backer->amount);
                    $project->decrement('backers_count');
                }
            }
        });
    }
}
