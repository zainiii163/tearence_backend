<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PackageResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="PackageResource")
 * )
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="favorite_id", type="integer", format="integer"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="price", type="integer", format="integer"),
     * @OA\Property(property="listing_days", type="integer", format="integer"),
     * @OA\Property(property="promo_days", type="integer", format="integer"),
     * @OA\Property(property="promo_show_promoted_area", type="string", format="string"),
     * @OA\Property(property="promo_show_featured_area", type="string", format="string"),
     * @OA\Property(property="promo_show_at_top", type="string", format="string"),
     * @OA\Property(property="promo_sign", type="string", format="string"),
     * @OA\Property(property="recommended_sign", type="string", format="string"),
     * @OA\Property(property="auto_renewal", type="integer", format="integer"),
     * @OA\Property(property="pictures", type="integer", format="integer"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
