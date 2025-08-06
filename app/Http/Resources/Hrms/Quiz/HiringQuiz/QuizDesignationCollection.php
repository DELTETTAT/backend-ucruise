<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizDesignationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $collection = [
            'hiring_designation_list' => $this->collection->map(function ($value, $key) {
                return new QuizDesignationResource($value);
            }),
        ];

        return $collection;
    }
}
