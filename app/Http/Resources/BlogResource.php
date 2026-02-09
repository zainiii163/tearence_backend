<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BlogResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BlogResource")
 * )
 */
class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="blog_id", type="integer", format="integer"),
     * @OA\Property(property="title", type="string", format="string"),
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="keywords", type="string", format="string"),
     * @OA\Property(property="content", type="string", format="string"),
     * @OA\Property(property="image", type="string", format="string"),
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
