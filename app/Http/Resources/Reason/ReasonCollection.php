<?php

namespace App\Http\Resources\Reason;

use Illuminate\Http\Resources\Json\ResourceCollection;


class ReasonCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

       return [
            'reason_list' => ReasonResource::collection($this->collection),
        ];
       // return parent::toArray($request);
    }
}
