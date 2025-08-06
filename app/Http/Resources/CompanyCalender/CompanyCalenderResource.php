<?php

namespace App\Http\Resources\CompanyCalender;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCalenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date,
            'event_type' => $this->event_type,
        ];
    }
}
