<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ListingResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="ListingResource")
 * )
 */
class ListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="listing_id", type="integer", format="integer"),
     * @OA\Property(property="package_id", type="integer", format="integer"),
     * @OA\Property(property="customer_id", type="integer", format="integer"),
     * @OA\Property(property="location_id", type="integer", format="integer"),
     * @OA\Property(property="category_id", type="integer", format="integer"),
     * @OA\Property(property="currency_id", type="integer", format="integer"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="description", type="string", format="string"),
     * @OA\Property(property="media_url", type="string", format="string"),
     * @OA\Property(property="price", type="string", format="string"),
     * @OA\Property(property="is_has_store", type="boolean", default="false"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
