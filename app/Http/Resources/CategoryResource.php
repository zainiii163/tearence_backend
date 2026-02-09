<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="CategoryResource")
 * )
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="category_id", type="integer", format="integer"),
     * @OA\Property(property="parent_id", type="integer", format="integer"),
     * @OA\Property(property="name", type="string", format="string"),
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="icon", type="string", format="string"),
     * @OA\Property(property="description", type="string", format="string"),
     * @OA\Property(property="sort_order", type="integer", format="integer"),
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
