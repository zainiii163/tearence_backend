<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyUpsellResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'user_id' => $this->user_id,
            
            // Upsell Information
            'upsell_type' => $this->upsell_type,
            'upsell_type_label' => $this->when(isset($this->upsell_type), function() {
                $types = \App\Models\PropertyUpsell::getUpsellTypes();
                return $types[$this->upsell_type] ?? $this->upsell_type;
            }),
            
            // Pricing
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,
            
            // Duration
            'duration_days' => $this->duration_days,
            'duration' => $this->duration,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            
            // Payment Status
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at,
            
            // Status
            'status' => $this->status,
            'is_active' => $this->when(isset($this->status), function() {
                return $this->status === 'active' && 
                       $this->starts_at <= now() && 
                       $this->expires_at > now();
            }),
            
            // Benefits
            'benefits' => $this->benefits,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'property' => $this->when($this->relationLoaded('property'), function() {
                return [
                    'id' => $this->property->id,
                    'title' => $this->property->title,
                    'slug' => $this->property->slug,
                    'cover_image' => $this->property->cover_image ? asset('storage/' . $this->property->cover_image) : null,
                ];
            }),
            
            'user' => $this->when($this->relationLoaded('user'), function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
