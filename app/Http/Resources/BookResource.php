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
     * @OA\Property(property="currency", type="string", format="string"),
     * @OA\Property(property="formatted_price", type="string", format="string"),
     * @OA\Property(property="cover_image_url", type="string", format="string"),
     * @OA\Property(property="additional_images", type="array", @OA\Items(type="string")),
     * @OA\Property(property="book_type", type="string", format="string"),
     * @OA\Property(property="genre", type="string", format="string"),
     * @OA\Property(property="author_name", type="string", format="string"),
     * @OA\Property(property="author", ref="#/components/schemas/AuthorResource"),
     * @OA\Property(property="country", type="string", format="string"),
     * @OA\Property(property="language", type="string", format="string"),
     * @OA\Property(property="format", type="string", format="string"),
     * @OA\Property(property="isbn", type="string", format="string"),
     * @OA\Property(property="publisher", type="string", format="string"),
     * @OA\Property(property="publication_date", type="string", format="date"),
     * @OA\Property(property="pages", type="integer", format="integer"),
     * @OA\Property(property="age_range", type="string", format="string"),
     * @OA\Property(property="series_name", type="string", format="string"),
     * @OA\Property(property="edition", type="string", format="string"),
     * @OA\Property(property="purchase_links", type="array", @OA\Items(type="object")),
     * @OA\Property(property="trailer_video_url", type="string", format="string"),
     * @OA\Property(property="sample_files", type="array", @OA\Items(type="object")),
     * @OA\Property(property="rating", type="number", format="number"),
     * @OA\Property(property="views_count", type="integer", format="integer"),
     * @OA\Property(property="saves_count", type="integer", format="integer"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="advert_type", type="string", format="string"),
     * @OA\Property(property="expires_at", type="string", format="datetime"),
     * @OA\Property(property="verified_author", type="boolean", format="boolean"),
     * @OA\Property(property="is_saved", type="boolean", format="boolean"),
     * @OA\Property(property="upsells", type="array", @OA\Items(ref="#/components/schemas/BookUpsellResource")),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => $this->formatted_price,
            'cover_image_url' => $this->cover_image_url,
            'additional_images' => $this->additional_images,
            'book_type' => $this->book_type,
            'genre' => $this->genre,
            'author_name' => $this->author_name,
            'author' => $this->when($this->author, new AuthorResource($this->author)),
            'country' => $this->country,
            'language' => $this->language,
            'format' => $this->format,
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'publication_date' => $this->publication_date,
            'pages' => $this->pages,
            'age_range' => $this->age_range,
            'series_name' => $this->series_name,
            'edition' => $this->edition,
            'purchase_links' => $this->purchase_links,
            'trailer_video_url' => $this->trailer_video_url,
            'sample_files' => $this->sample_files,
            'rating' => $this->rating,
            'views_count' => $this->views_count,
            'saves_count' => $this->saves_count,
            'status' => $this->status,
            'advert_type' => $this->advert_type,
            'expires_at' => $this->expires_at,
            'verified_author' => $this->verified_author,
            'is_saved' => $this->when(isset($this->is_saved), $this->is_saved),
            'upsells' => BookUpsellResource::collection($this->whenLoaded('upsells')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
