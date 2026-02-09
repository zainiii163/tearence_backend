<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ErrorResource
 * 
 * @OA\Schema(
 *  @OA\Xml(name="ErrorResource")
 * )
 */
class ErrorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    /**
     *   @OA\Property(property="status", type="string", format="string", default="Error"),
     *   @OA\Property(property="message", type="string", format="string", default="error message"),
     *   @OA\Property(property="data", type="object"),
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
