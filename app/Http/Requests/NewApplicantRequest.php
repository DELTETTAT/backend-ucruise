<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NewApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            //'email' => 'required|email|unique:new_applicant,email,' . $this->route('id'),
            'email' => 'required|email|',  //// unique:users,email
            'phone' => 'required|string|max:15',
            'gender' => 'required|string|max:10',
            'dob' => 'nullable|date',
            'designation_id' => 'required|integer',
           // 'role' => 'required|integer',
            'linkedin_url' => 'nullable|string',
           // 'upload_resume' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'upload_resume' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf|max:10240',
            'cover_letter' => 'nullable|string',
            'skills' => 'nullable',
            'experience' => 'required|string',
            'typing_speed' => 'nullable|integer',
            'is_notice' => 'nullable|integer',
            'notice_period' => 'nullable|string|max:255',
            'expected_date_of_join' => 'nullable|date',
            'working_nightshift' => 'nullable|integer',
            'cab_facility' => 'nullable|integer',
            'referral_name' => 'nullable|string|max:255',
            'employee_code' => 'nullable|string|max:255',
            'current_salary' => 'nullable|numeric',
            'salary_expectation' => 'nullable|numeric',
            'why_do_you_want_to_join_unify' => 'nullable|string',
            'how_do_you_come_to_know_about_unify' => 'nullable|string|max:255',
            'weakness' => 'nullable|string',
            'strength' => 'nullable|string',
            'stages' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|max:255',
            'country' => 'nullable',
            'cab_address.cab_address' => 'nullable|string|max:255',
            'cab_address.longitude.' => 'nullable|max:255',
            'cab_address.latitude' => 'nullable',
            'cab_address.locality' => 'nullable|max:255',
            'is_rejected' => 'nullable|integer',
            'is_feature_reference' => 'nullable|integer',
            'blood_group' => 'nullable|string',
           // 'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_employee' => 'nullable|integer',
            'marital_status' => 'nullable|string',
            'is_accepted' => 'nullable|integer',
            'reason' => 'nullable|string',
            'interview_mode' => 'nullable|integer|in:0,1', // 0 => offline, 1 => inline
            'referral_code' => 'nullable|string',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'error' => 'Error list message',
            'message' => $firstError,
        ], 422));
    }
}
