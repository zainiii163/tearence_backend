<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AuthorResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="AuthorResource")
 * )
 */
class AuthorResource extends JsonResource
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
     * @OA\Property(property="bio", type="string", format="string"),
     * @OA\Property(property="photo_url", type="string", format="string"),
     * @OA\Property(property="email", type="string", format="string"),
     * @OA\Property(property="website", type="string", format="string"),
     * @OA\Property(property="social_links", type="array", @OA\Items(type="object")),
     * @OA\Property(property="country", type="string", format="string"),
     * @OA\Property(property="verified", type="boolean", format="boolean"),
     * @OA\Property(property="books_count", type="integer", format="integer"),
     * @OA\Property(property="average_rating", type="number", format="number"),
     * @OA\Property(property="total_reviews", type="integer", format="integer"),
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
            'bio' => $this->bio,
            'photo_url' => $this->photo_url,
            'email' => $this->email,
            'website' => $this->website,
            'social_links' => $this->social_links,
            'country' => $this->country,
            'verified' => $this->verified,
            'books_count' => $this->when(isset($this->books_count), $this->books_count),
            'average_rating' => $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
