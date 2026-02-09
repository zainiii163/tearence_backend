<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CampaignResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="CampaignResource")
 * )
 */
class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="customer_id", type="integer", format="integer"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="code", type="string", format="string"),
     * @OA\Property(property="thumbnail", type="string", format="string"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="description", type="string", format="string"),
     * @OA\Property(property="target", type="integer", format="integer"),
     * @OA\Property(property="collected", type="integer", format="integer"),
     * @OA\Property(property="donors", type="integer", format="integer"),
     * @OA\Property(property="views", type="integer", format="integer"),
     * @OA\Property(property="last_donation", type="string", format="string"),
     * @OA\Property(property="location", type="string", format="string"),
     * @OA\Property(property="target_date", type="string", format="string"),
     * @OA\Property(property="deleted_at", type="string", format="string"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
