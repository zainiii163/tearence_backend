<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AffiliateResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="AffiliateResource")
 * )
 */
class AffiliateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="position", type="string", format="string"),
     * @OA\Property(property="link", type="string", format="string"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="customer_id", type="integer", format="integer"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
