<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class LocationResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="LocationResource")
 * )
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="location_id", type="integer", format="integer"),
     * @OA\Property(property="country_id", type="integer", format="integer"),
     * @OA\Property(property="zone_id", type="integer", format="integer"),
     * @OA\Property(property="city", type="string", format="string"),
     * @OA\Property(property="zip", type="string", format="string"),
     * @OA\Property(property="latitude", type="number", format="number"),
     * @OA\Property(property="longitude", type="number", format="number"),
     * @OA\Property(property="retries", type="integer", format="integer"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
