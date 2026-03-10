<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleCategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'vehicles_count' => $this->when(isset($this->vehicles_count), $this->vehicles_count),
            'vehicles' => $this->whenLoaded('vehicles', function () {
                return VehicleResource::collection($this->vehicles);
            }),
            
            // Computed properties
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'icon_url' => $this->icon ? asset('storage/' . $this->icon) : null,
        ];
    }
}
