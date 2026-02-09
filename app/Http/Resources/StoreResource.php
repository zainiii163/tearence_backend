<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class StoreResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="StoreResource")
 * )
 */
class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="store_id", type="integer", format="integer"),
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="customer_id", type="integer", format="integer"),
     * @OA\Property(property="store_name", type="string", format="string"),
     * @OA\Property(property="company_name", type="string", format="string"),
     * @OA\Property(property="company_no", type="string", format="string"),
     * @OA\Property(property="vat", type="string", format="string"),
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
