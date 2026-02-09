<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BusinessResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="BusinessResource")
 * )
 */
class BusinessResource extends JsonResource
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
     * @OA\Property(property="slug", type="string", format="string"),
     * @OA\Property(property="business_name", type="string", format="string"),
     * @OA\Property(property="business_phone", type="string", format="string"),
     * @OA\Property(property="business_address", type="string", format="string"),
     * @OA\Property(property="business_email", type="string", format="string"),
     * @OA\Property(property="business_logo", type="string", format="string"),
     * @OA\Property(property="business_website", type="string", format="string"),
     * @OA\Property(property="business_owner", type="string", format="string"),
     * @OA\Property(property="personal_phone_number", type="string", format="string"),
     * @OA\Property(property="personal_email", type="string", format="string"),
     * @OA\Property(property="business_company_registration", type="string", format="string"),
     * @OA\Property(property="business_company_name", type="string", format="string"),
     * @OA\Property(property="business_company_no", type="string", format="string"),
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
