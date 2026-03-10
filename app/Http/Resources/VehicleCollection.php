<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ],
            'filters' => [
                'advert_types' => [
                    ['value' => 'sale', 'label' => 'For Sale'],
                    ['value' => 'hire', 'label' => 'For Hire'],
                    ['value' => 'lease', 'label' => 'For Lease'],
                    ['value' => 'transport_service', 'label' => 'Transport Service'],
                ],
                'fuel_types' => [
                    ['value' => 'petrol', 'label' => 'Petrol'],
                    ['value' => 'diesel', 'label' => 'Diesel'],
                    ['value' => 'electric', 'label' => 'Electric'],
                    ['value' => 'hybrid', 'label' => 'Hybrid'],
                    ['value' => 'lpg', 'label' => 'LPG'],
                    ['value' => 'other', 'label' => 'Other'],
                ],
                'transmissions' => [
                    ['value' => 'manual', 'label' => 'Manual'],
                    ['value' => 'automatic', 'label' => 'Automatic'],
                    ['value' => 'semi-automatic', 'label' => 'Semi-Automatic'],
                    ['value' => 'cvt', 'label' => 'CVT'],
                ],
                'conditions' => [
                    ['value' => 'new', 'label' => 'New'],
                    ['value' => 'used', 'label' => 'Used'],
                    ['value' => 'excellent', 'label' => 'Excellent'],
                    ['value' => 'good', 'label' => 'Good'],
                    ['value' => 'fair', 'label' => 'Fair'],
                ],
                'body_types' => [
                    ['value' => 'saloon', 'label' => 'Saloon'],
                    ['value' => 'hatchback', 'label' => 'Hatchback'],
                    ['value' => 'suv', 'label' => 'SUV'],
                    ['value' => 'mpv', 'label' => 'MPV'],
                    ['value' => 'coupe', 'label' => 'Coupe'],
                    ['value' => 'convertible', 'label' => 'Convertible'],
                    ['value' => 'pickup', 'label' => 'Pickup'],
                    ['value' => 'van', 'label' => 'Van'],
                    ['value' => 'truck', 'label' => 'Truck'],
                    ['value' => 'bus', 'label' => 'Bus'],
                    ['value' => 'motorbike', 'label' => 'Motorbike'],
                    ['value' => 'boat', 'label' => 'Boat'],
                    ['value' => 'other', 'label' => 'Other'],
                ],
                'price_types' => [
                    ['value' => 'fixed', 'label' => 'Fixed Price'],
                    ['value' => 'per_day', 'label' => 'Per Day'],
                    ['value' => 'per_week', 'label' => 'Per Week'],
                    ['value' => 'per_month', 'label' => 'Per Month'],
                    ['value' => 'negotiable', 'label' => 'Negotiable'],
                ],
                'sort_options' => [
                    ['value' => 'created_at', 'label' => 'Most Recent'],
                    ['value' => 'price', 'label' => 'Price: Low to High'],
                    ['value' => '-price', 'label' => 'Price: High to Low'],
                    ['value' => 'year', 'label' => 'Year: Newest First'],
                    ['value' => '-year', 'label' => 'Year: Oldest First'],
                    ['value' => 'mileage', 'label' => 'Mileage: Low to High'],
                    ['value' => '-mileage', 'label' => 'Mileage: High to Low'],
                    ['value' => 'views_count', 'label' => 'Most Viewed'],
                    ['value' => 'saves_count', 'label' => 'Most Saved'],
                ],
            ],
        ];
    }
}
