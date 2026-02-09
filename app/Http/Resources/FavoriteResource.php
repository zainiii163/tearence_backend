<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FavoriteResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="FavoriteResource")
 * )
 */
class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="favorite_id", type="integer", format="integer"),
     * @OA\Property(property="customer_id", type="integer", format="integer"),
     * @OA\Property(property="listing_id", type="integer", format="integer"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
