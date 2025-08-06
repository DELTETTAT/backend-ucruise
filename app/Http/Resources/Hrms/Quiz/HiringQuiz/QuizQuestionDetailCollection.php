<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use App\Models\QuizAnswerDetail;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QuizQuestionDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $collection = $this->collection->map(function ($value, $key) {
            return new QuizQuestionDetailResource($value);
        });

        return $collection;
    }
}
