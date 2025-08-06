<?php

namespace App\Http\Resources\Hrms\Quiz\QuestionType;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestiontypeResource extends JsonResource
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
            "id" => $this->id,
            "title" => $this->title,
            "status" => $this->status == 1 ? "Active" : "Inactive"
        ];
    }
}
