<?php

namespace App\Http\Resources\Announcement;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
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
            'send_to' => $this->send_to,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'subject' => $this->subject,
            'status' => $this->status,
        ];
    }

}
