<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CustomerResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="CustomerResource")
 * )
 */
class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="customer_uid", type="string", format="string"),
     * @OA\Property(property="group_id", type="integer", format="integer"),
     * @OA\Property(property="first_name", type="string", format="string"),
     * @OA\Property(property="last_name", type="string", format="string"),
     * @OA\Property(property="company_name", type="string", format="string"),
     * @OA\Property(property="company_number", type="string", format="string"),
     * @OA\Property(property="vat_number", type="string", format="string"),
     * @OA\Property(property="customer_type", type="string", format="string"),
     * @OA\Property(property="email", type="string", format="string"),
     * @OA\Property(property="phone", type="integer", format="integer"),
     * @OA\Property(property="birthday", type="integer", format="integer"),
     * @OA\Property(property="avatar", type="integer", format="integer"),
     * @OA\Property(property="source", type="integer", format="integer"),
     * @OA\Property(property="status", type="integer", format="integer"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     *
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
