<?php

namespace App\Http\Resources\Hrms\Quiz\HiringQuiz;

use App\Models\QuizAnswerDetail;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $getAnswerOptionsDetails = QuizAnswerDetail::where("quiz_question_detail_id", $this->id)->get();
        return [
            "id" => $this->id,
            "question" => $this->question,
            "description" => $this->description,
            "question_type_id" => $this->question_type_id,
            "answer_options" => $getAnswerOptionsDetails
        ];
    }
}
