<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicantRequest extends FormRequest
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
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|',  //// unique:users,email
            'address' => 'nullable|string|max:255',
            'country' => 'nullable',
            'state' => 'nullable|max:255',
            'city' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|integer|in:0,1,2', // 0 =>
            'marital_status' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'designation_id' => 'nullable',
            'experience' => 'nullable|string',
            'linkedin_url' => 'nullable|string',
            'upload_resume' => 'nullable',
            'cover_letter' => 'nullable|string',
            'skills' => 'nullable',
            'typing_speed' => 'nullable|integer',
            'notice_period' => 'nullable|string|max:255',
            'expected_date_of_join' => 'nullable|date',
            'interview_mode' => 'nullable|integer|in:0,1', // 0 => offline, 1 => inline
            'working_nightshift' => 'nullable|integer',
            'cab_facility' => 'nullable|integer',
            'cab_address' => 'nullable|string|max:255',
            'longitude' => 'nullable|max:255',
            'latitude' => 'nullable',
            'locality' => 'nullable|max:255',
            'referral_code' => 'nullable|string',
            'referral_name' => 'nullable|string|max:255',
            'employee_code' => 'nullable|string|max:255',
            'current_salary' => 'nullable|numeric',
            'salary_expectation' => 'nullable|numeric',
            'why_do_you_want_to_join_unify' => 'nullable|string',
            'how_do_you_come_to_know_about_unify' => 'nullable|string|max:255',
            'weakness' => 'nullable|string',
            'strength' => 'nullable|string',
            'note' => 'required|string|max:255',
            // 'role' => 'nullable|integer',
            // 'is_notice' => 'nullable|integer',
            // 'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'reason' => 'nullable|string',
            // 'cab_address.cab_address' => 'nullable|string|max:255',
            // 'cab_address.longitude.' => 'nullable|max:255',
            // 'cab_address.latitude' => 'nullable',
            // 'cab_address.locality' => 'nullable|max:255',
        ];
    }
}
