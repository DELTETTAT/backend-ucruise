<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MakePermanentEmployeeRequest extends FormRequest
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email|unique:sub_users,email',
            'address' => 'required|string',
            'country' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'employee_shift' => 'required|string',
            'marital_status' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'skill' => 'nullable',
            'experience' => 'nullable|string',
            'company_email' => 'nullable|email',
            'id_card' => 'nullable|string|unique:sub_users,unique_id',
            'date_of_join' => 'required|date',
            'assign_employee_position' => 'required|integer', //  using for Role like Employee, Manager, HR
            'manager_id' => 'nullable|integer', //
            'assign_department' => 'required|string', //   using for Designation like PHP Developer, React Developer
            'assign_team' => 'nullable|integer', //
            'assign_pc' => 'nullable|string',
            'new_applicant_id' => 'required|integer|exists:new_applicant,id',
            'cab_facility' => 'required|integer|in:0,1',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pickup_address' => 'nullable|string',
            'profile_image' => 'nullable|string',
        ];
    }
}
