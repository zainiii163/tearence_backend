<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BookCategoryResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BookCategoryResource")
 * )
 */
class BookCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="name", type="string", format="string"),
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="description", type="string", format="string"),
     * @OA\Property(property="icon", type="string", format="string"),
     * @OA\Property(property="color", type="string", format="string"),
     * @OA\Property(property="image_url", type="string", format="string"),
     * @OA\Property(property="books_count", type="integer", format="integer"),
     * @OA\Property(property="active_books_count", type="integer", format="integer"),
     * @OA\Property(property="is_active", type="boolean", format="boolean"),
     * @OA\Property(property="sort_order", type="integer", format="integer"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'image_url' => $this->image_url,
            'books_count' => $this->when(isset($this->books_count), $this->books_count),
            'active_books_count' => $this->active_books_count,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
