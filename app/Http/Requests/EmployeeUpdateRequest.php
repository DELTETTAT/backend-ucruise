<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:sub_users,email,'. $id,
            'employee_id' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'position' => 'nullable|integer',             //|integer|exists:designations,id',
            'gender' => 'required|string',
            'dob' => 'required|date',
            'profile_image' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp,bmp|max:5120',
            'marital_status' => 'required',
            'blood_group' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'experience' => 'nullable|string',
            'doj' => 'required|date',
            'role' => 'required|integer|exists:hrms_roles,id',
        ];
    }
}
