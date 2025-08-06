<?php

namespace App\Http\Resources\NewApplicant;

use Illuminate\Http\Resources\Json\JsonResource;

class CandidatesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $offered = $this->is_offered  === 1 ? 'Offered' : ($this->stages === 4 ? 'Offered' : '') ;
        return [
            'id'=> $this->id,
            'first_name'=> $this->first_name,
            'last_name'=> $this->last_name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'gender'=> $this->gender === 1 ? 'Male' : ($this->gender === 0 ? 'Female' : 'other'),
            'dob'=> $this->dob,
            'role'=> $this->role == 1 ? 'Junior Level' : ($this->role == 2 ? 'Medium Level' : ($this->role == 3 ? 'Senior Level' : 'Unknown')),
            'linkedin_url'=> $this->linkedin_url,
            'upload_resume'=> $this->upload_resume,
            'cover_letter'=> $this->cover_letter,
            'skills'=> $this->skills,
            'experience'=> $this->experience,
            'typing_speed'=> $this->typing_speed,
            'notice_period'=> $this->notice_period,
            'is_notice'=> $this->is_notice,
            'expected_date_of_join'=> $this->expected_date_of_join,
            'working_nightshift'=> $this->working_nightshift,
            'cab_facility'=> $this->cab_facility,
            'referral_name'=> $this->referral_name,
            'referral_code'=> $this->referral_code,
            'employee_code'=> $this->employee_code,
            'current_salary'=> $this->current_salary,
            'salary_expectation'=> $this->salary_expectation,
            'why_do_you_want_to_join_unify'=> $this->why_do_you_want_to_join_unify,
            'how_do_you_come_to_know_about_unify'=> $this->how_do_you_come_to_know_about_unify,
            'weakness'=> $this->weakness,
            'strength'=> $this->strength,
            'address'=> $this->address,
            'city'=> $this->city,
            'state'=> $this->state,
            'country'=> $this->country,
            'stages'=> $this->stages,
            'is_rejected' =>$this->is_rejected,
            'is_feature_reference' =>$this->is_feature_reference,
            'applied_date'=> $this->created_at->format('Y-m-d'),
            //'image'=> asset($this->image) ? asset('designation/' . $this->image) : "No Image",
            'appliedTo' => $this->designation ?  $this->designation->title : null,
            'designation_id' => $this->designation_id,
            'blood_group' => $this->blood_group,
            'profile_image' =>  asset($this->profile_image) ? asset("profile_image/" . $this->profile_image) : "No Image",
            'is_employee' => $this->is_employee,
            'marital_status' => $this->marital_status,
            'question_count' => $this->question_count,
            'correct answers' => $this->correct_answers,
            'interview_mode' => $this->interview_mode,
            'reason_history' => $this->reason_history,
            'exists_history' => $this->exists_history,
            'is_offered' => $this->is_offered,
            'manager' => $this->manager,
            'OfferedHistory' => $this->OfferedHistory,
            'sent_mail' => $this->sent_mail,
            'is_accept' => $this->is_accept === 1 ? 'Accepted' : ($this->is_accept === 0 ? 'Declined' : $offered),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        //return parent::toArray($request);
    }
}
