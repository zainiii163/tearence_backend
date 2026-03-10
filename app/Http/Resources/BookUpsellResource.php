<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BookUpsellResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BookUpsellResource")
 * )
 */
class BookUpsellResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="book_id", type="integer", format="integer"),
     * @OA\Property(property="upsell_type", type="string", format="string"),
     * @OA\Property(property="price", type="number", format="number"),
     * @OA\Property(property="currency", type="string", format="string"),
     * @OA\Property(property="formatted_price", type="string", format="string"),
     * @OA\Property(property="duration_days", type="integer", format="integer"),
     * @OA\Property(property="starts_at", type="string", format="datetime"),
     * @OA\Property(property="expires_at", type="string", format="datetime"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="benefits", type="array", @OA\Items(type="string")),
     * @OA\Property(property="payment_reference", type="string", format="string"),
     * @OA\Property(property="payment_date", type="string", format="datetime"),
     * @OA\Property(property="remaining_days", type="integer", format="integer"),
     * @OA\Property(property="is_active", type="boolean", format="boolean"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'upsell_type' => $this->upsell_type,
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => $this->formatted_price,
            'duration_days' => $this->duration_days,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'status' => $this->status,
            'benefits' => $this->benefits,
            'payment_reference' => $this->payment_reference,
            'payment_date' => $this->payment_date,
            'remaining_days' => $this->remaining_days,
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
