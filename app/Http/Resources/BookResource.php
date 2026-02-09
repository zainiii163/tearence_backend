<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BookResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BookResource")
 * )
 */
class BookResource extends JsonResource
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
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="description", type="string", format="string"),
     * @OA\Property(property="short_description", type="string", format="string"),
     * @OA\Property(property="price", type="number", format="number"),
     * @OA\Property(property="image_url", type="string", format="string"),
     * @OA\Property(property="link_url", type="string", format="string"),
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
