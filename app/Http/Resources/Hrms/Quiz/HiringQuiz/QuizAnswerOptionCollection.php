<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizAnswerOptionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
