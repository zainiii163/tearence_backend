<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DonorResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="DonorResource")
 * )
 */
class DonorResource extends JsonResource
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
     * @OA\Property(property="campaign_id", type="integer", format="integer"),
     * @OA\Property(property="anonymous", type="integer", format="integer"),
     * @OA\Property(property="amount", type="integer", format="integer"),
     * @OA\Property(property="fee", type="integer", format="integer"),
     * @OA\Property(property="message", type="string", format="string"),
     * @OA\Property(property="paid", type="integer", format="integer"),
     * @OA\Property(property="uuid", type="string", format="string"),
     * @OA\Property(property="ref_id", type="string", format="string"),
     * @OA\Property(property="payment_method", type="string", format="string"),
     * @OA\Property(property="payment_url", type="integer", format="integer"),
     * @OA\Property(property="payment_json", type="string", format="string"),
     * @OA\Property(property="expired_at", type="string", format="string"),
     * @OA\Property(property="paid_at", type="string", format="string"),
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
