<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            
            // Basic Information
            'title' => $this->title,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'category' => $this->category,
            'property_type' => $this->property_type,
            'property_type_label' => $this->when(isset($this->property_type), function() {
                $types = \App\Models\Property::getPropertyTypes();
                return $types[$this->property_type] ?? $this->property_type;
            }),
            
            // Location
            'country' => $this->country,
            'city' => $this->city,
            'region' => $this->region,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'show_exact_location' => $this->show_exact_location,
            'full_address' => $this->full_address,
            
            // Residential Specifications
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'property_size' => $this->property_size,
            'size_unit' => $this->size_unit,
            'formatted_size' => $this->formatted_size,
            'furnished' => $this->furnished,
            'parking_spaces' => $this->parking_spaces,
            
            // Commercial Specifications
            'commercial_type' => $this->commercial_type,
            'commercial_type_label' => $this->when(isset($this->commercial_type), function() {
                $types = \App\Models\Property::getCommercialTypes();
                return $types[$this->commercial_type] ?? $this->commercial_type;
            }),
            'floor_area' => $this->floor_area,
            'footfall_rating' => $this->footfall_rating,
            'accessibility_features' => $this->accessibility_features,
            
            // Industrial Specifications
            'zoning_type' => $this->zoning_type,
            'warehouse_size' => $this->warehouse_size,
            'loading_bays' => $this->loading_bays,
            'power_capacity' => $this->power_capacity,
            'ceiling_height' => $this->ceiling_height,
            
            // Land Specifications
            'land_size' => $this->land_size,
            'land_type' => $this->land_type,
            'land_type_label' => $this->when(isset($this->land_type), function() {
                $types = \App\Models\Property::getLandTypes();
                return $types[$this->land_type] ?? $this->land_type;
            }),
            'planning_permission' => $this->planning_permission,
            'planning_permission_label' => $this->when(isset($this->planning_permission), function() {
                $types = \App\Models\Property::getPlanningPermissions();
                return $types[$this->planning_permission] ?? $this->planning_permission;
            }),
            'soil_quality' => $this->soil_quality,
            
            // Luxury Specifications
            'premium_features' => $this->premium_features,
            'security_features' => $this->security_features,
            'view_type' => $this->view_type,
            'view_type_label' => $this->when(isset($this->view_type), function() {
                $types = \App\Models\Property::getViewTypes();
                return $types[$this->view_type] ?? $this->view_type;
            }),
            
            // Investment Specifications
            'rental_yield' => $this->rental_yield,
            'occupancy_rate' => $this->occupancy_rate,
            'current_rental_income' => $this->current_rental_income,
            'roi_percentage' => $this->roi_percentage,
            
            // Pricing
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,
            'negotiable' => $this->negotiable,
            'deposit_required' => $this->deposit_required,
            'service_charges' => $this->service_charges,
            'maintenance_fees' => $this->maintenance_fees,
            
            // Media
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'additional_images' => $this->when($this->additional_images, function() {
                return collect($this->additional_images)->map(function($image) {
                    return asset('storage/' . $image);
                })->toArray();
            }),
            'video_tour_link' => $this->video_tour_link,
            
            // Description
            'description' => $this->description,
            'overview' => $this->overview,
            'key_features' => $this->key_features,
            'location_highlights' => $this->location_highlights,
            'nearby_amenities' => $this->nearby_amenities,
            'transport_links' => $this->transport_links,
            'additional_notes' => $this->additional_notes,
            'amenities' => $this->amenities,
            
            // Seller/Agent Information
            'seller_name' => $this->seller_name,
            'seller_company' => $this->seller_company,
            'seller_phone' => $this->seller_phone,
            'seller_email' => $this->seller_email,
            'seller_website' => $this->seller_website,
            'seller_logo' => $this->seller_logo ? asset('storage/' . $this->seller_logo) : null,
            'verified_agent' => $this->verified_agent,
            
            // Status and Visibility
            'status' => $this->status,
            'featured' => $this->featured,
            'promoted' => $this->promoted,
            'sponsored' => $this->sponsored,
            'is_featured' => $this->is_featured,
            'is_promoted' => $this->is_promoted,
            'is_sponsored' => $this->is_sponsored,
            'featured_until' => $this->featured_until,
            'promoted_until' => $this->promoted_until,
            'sponsored_until' => $this->sponsored_until,
            
            // Analytics
            'views_count' => $this->views_count,
            'inquiries_count' => $this->inquiries_count,
            'saves_count' => $this->saves_count,
            
            // Approval
            'approval_status' => $this->approval_status,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->approved_by,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'user' => $this->when($this->relationLoaded('user'), function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'category_info' => $this->when($this->relationLoaded('category'), function() {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            'upsells' => $this->when($this->relationLoaded('upsells'), function() {
                return PropertyUpsellResource::collection($this->upsells);
            }),
            
            // Additional computed fields
            'is_saved' => $this->when(auth()->check(), function() {
                return auth()->user()->savedProperties()->where('property_id', $this->id)->exists();
            }),
        ];
    }
}
