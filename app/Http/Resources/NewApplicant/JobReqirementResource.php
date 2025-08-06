<?php

namespace App\Http\Resources\NewApplicant;
use App\Http\Resources\Hrms\Quiz\QuizLevel\QuizLevelResource;
use App\Models\QuizLevel;
use Illuminate\Http\Resources\Json\JsonResource;

class JobReqirementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $quizLevel = $this->whenLoaded('quizLevel'); 
        $quizLevelData = $quizLevel ? $quizLevel : QuizLevel::find($this->roles);
        return [
            'id'=> $this->id,
            'name' => $this->name,
            'employee_id' => $this->employee_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'job_title' => optional($this->Designation)->title ?? 'Unknown Designation',
            'roles' => $this->roles,
            'job_type' => $this->job_type,
            'no_of_required_emp' => $this->no_of_required_emp,
            'gender' => $this->gender,
            'priority' => $this->priority,
            'status' => $this->status,
            'job_description' => $this->job_description,
            'justify_need' => $this->justify_need,
            'qualifications' => $this->qualifications,
            'benefits' => $this->benefits,
            'start_date' => $this->start_date,
            'deadline' => $this->deadline,
            'shift_schedule' => $this->shift_schedule,
            'post_status' => $this->post_status,
            'work_type' => $this->work_type,
            'pay' => $this->pay,
            'quiz_level' => $quizLevelData ? new QuizLevelResource($quizLevelData) : null,
        ];
        
    }
}
