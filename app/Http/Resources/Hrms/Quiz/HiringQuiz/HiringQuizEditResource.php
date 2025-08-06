<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use App\Models\QuizAnswerDetail;
use Illuminate\Http\Resources\Json\JsonResource;

class HiringQuizEditResource extends JsonResource
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
            "name" => $this->name,
            "description" => $this->description,
            "designation_id" => $this->desgination_id,
            "quiz_level" => $this->quiz_level_id,
            "questions_details" => new QuizQuestionDetailCollection($this->getQuizQuestionDetails),
        ];
    }
}
