<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_program_id',
        'click_id',
        'amount',
        'commission_rate',
        'commission_amount',
        'conversion_type',
        'product_name',
        'customer_email',
        'transaction_id',
        'converted_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }

    public function click(): BelongsTo
    {
        return $this->belongsTo(AffiliateClick::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helper methods
    public function getFormattedAmount(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedCommission(): string
    {
        return '$' . number_format($this->commission_amount, 2);
    }

    public function getStatusText(): string
    {
        $statuses = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'rejected' => 'Rejected',
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getConversionTypeText(): string
    {
        $types = [
            'sale' => 'Sale',
            'lead' => 'Lead',
            'signup' => 'Sign Up',
            'download' => 'Download',
            'other' => 'Other',
        ];

        return $types[$this->conversion_type] ?? 'Other';
    }
}
