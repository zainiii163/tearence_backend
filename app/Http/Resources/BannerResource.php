<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BannerResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BannerResource")
 * )
 */
class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="url_link", type="string", format="string"),
     * @OA\Property(property="img", type="string", format="string"),
     * @OA\Property(property="size_img", type="string", format="string"),
     * @OA\Property(property="created_by", type="number", format="number"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
