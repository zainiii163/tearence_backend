<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="UserResource")
 * )
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     * 
     * @OA\Property(property="id", type="integer", format="integer"),
     * @OA\Property(property="first_name", type="string", format="string"),
     * @OA\Property(property="last_name", type="string", format="string"),
     * @OA\Property(property="email", type="string", format="string"),
     * @OA\Property(property="password", type="string", format="string"),
     * @OA\Property(property="verification_token", type="string", format="string"),
     * @OA\Property(property="status", type="string", format="string"),
     * @OA\Property(property="avatar", type="string", format="string"),
     * @OA\Property(property="created_at", type="string", format="string"),
     * @OA\Property(property="updated_at", type="string", format="string"),
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
