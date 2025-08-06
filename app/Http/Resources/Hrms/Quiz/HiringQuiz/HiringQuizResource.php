<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use Illuminate\Http\Resources\Json\JsonResource;

class HiringQuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
