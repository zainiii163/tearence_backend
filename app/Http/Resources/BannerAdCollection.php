<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BannerAdCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => BannerAdResource::collection($this->collection),
            'meta' => [
                'count' => $this->collection->count(),
                'total' => method_exists($this->resource, 'total') ? $this->resource->total() : $this->collection->count(),
                'per_page' => method_exists($this->resource, 'perPage') ? $this->resource->perPage() : null,
                'current_page' => method_exists($this->resource, 'currentPage') ? $this->resource->currentPage() : null,
                'last_page' => method_exists($this->resource, 'lastPage') ? $this->resource->lastPage() : null,
            ]
        ];
    }
}
