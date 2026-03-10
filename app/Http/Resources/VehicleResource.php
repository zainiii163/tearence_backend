<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'business_id' => $this->business_id,
            'category_id' => $this->category_id,
            'make_id' => $this->make_id,
            'model_id' => $this->model_id,
            
            // Basic Information
            'title' => $this->title,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'advert_type' => $this->advert_type,
            'condition' => $this->condition,
            
            // Vehicle Specifications
            'year' => $this->year,
            'mileage' => $this->mileage,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'engine_size' => $this->engine_size,
            'color' => $this->color,
            'doors' => $this->doors,
            'seats' => $this->seats,
            'body_type' => $this->body_type,
            'vin' => $this->vin,
            'registration_number' => $this->registration_number,
            
            // Commercial Vehicle Specific
            'payload_capacity' => $this->payload_capacity,
            'axles' => $this->axles,
            'emission_class' => $this->emission_class,
            
            // Boat Specific
            'length' => $this->length,
            'engine_type' => $this->engine_type,
            'capacity' => $this->capacity,
            'trailer_included' => $this->trailer_included,
            
            // Transport Service Specific
            'service_area' => $this->service_area,
            'operating_hours' => $this->operating_hours,
            'passenger_capacity' => $this->passenger_capacity,
            'luggage_capacity' => $this->luggage_capacity,
            'airport_pickup' => $this->airport_pickup,
            
            // Pricing
            'price' => $this->price,
            'price_type' => $this->price_type,
            'negotiable' => $this->negotiable,
            'deposit' => $this->deposit,
            
            // Media
            'main_image' => $this->main_image_url,
            'additional_images' => $this->additional_images_urls,
            'video_link' => $this->video_link,
            
            // Location
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'show_exact_location' => $this->show_exact_location,
            'location' => $this->location,
            
            // Contact
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'website' => $this->website,
            
            // Additional
            'features' => $this->features,
            'service_history' => $this->service_history,
            'mot_expiry' => $this->mot_expiry,
            'road_tax_status' => $this->road_tax_status,
            'previous_owners' => $this->previous_owners,
            
            // Status and Analytics
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_promoted' => $this->is_promoted,
            'is_featured' => $this->is_featured,
            'is_sponsored' => $this->is_sponsored,
            'is_top_of_category' => $this->is_top_of_category,
            'views' => $this->views,
            'clicks' => $this->clicks,
            'saves' => $this->saves,
            'enquiries' => $this->enquiries,
            
            // Payment and Expiry
            'pricing_plan_id' => $this->pricing_plan_id,
            'payment_status' => $this->payment_status,
            'paid_amount' => $this->paid_amount,
            'payment_transaction_id' => $this->payment_transaction_id,
            'paid_at' => $this->paid_at,
            'expires_at' => $this->expires_at,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Computed properties
            'full_name' => $this->full_name,
            'display_price' => $this->display_price,
            'upgrade_badges' => $this->upgrade_badges,
            'is_currently_active' => $this->isCurrentlyActive(),
            
            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->category->icon,
                ];
            }),
            'make' => $this->whenLoaded('make', function () {
                return [
                    'id' => $this->make->id,
                    'name' => $this->make->name,
                    'slug' => $this->make->slug,
                    'country' => $this->make->country,
                    'logo' => $this->make->logo,
                ];
            }),
            'vehicle_model' => $this->whenLoaded('vehicleModel', function () {
                return [
                    'id' => $this->vehicleModel->id,
                    'name' => $this->vehicleModel->name,
                    'slug' => $this->vehicleModel->slug,
                    'category' => $this->vehicleModel->category,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone ?? null,
                ];
            }),
            'business' => $this->whenLoaded('business', function () {
                return [
                    'id' => $this->business->id,
                    'name' => $this->business->name,
                    'logo' => $this->business->logo,
                    'website' => $this->business->website,
                ];
            }),
            'is_favourited' => $this->when(auth()->check(), function () {
                return $this->isFavouritedBy(auth()->id());
            }),
        ];
    }
}
