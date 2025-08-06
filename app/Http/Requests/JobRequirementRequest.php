<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequirementRequest extends FormRequest
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
            //'name' => 'required|string',
            //'employee_id' => 'required|string',
            //'email' => 'required|email',
            //'phone' => 'required|string',
           // 'company_name' => 'required|string',
           // 'address' => 'required|string',
            //'no_of_required_emp' => 'required|integer',
            //'gender' => 'integer',
            //'justify_need' => 'string',
           // 'qualifications' => 'required|string',
           // 'benefits' => 'required|string',
           // 'deadline' => 'required|string',
           // 'shift_schedule' => 'required|string',
           // 'work_type' => 'required|string',
           // 'pay' => 'required|string',

           'designation_id' => 'required|integer',
           'roles' => 'required|integer',
           'job_type' => 'required|integer',
           'post_status' => 'required|integer',
           'priority' => 'required|integer',
           //'status' => 'required|integer',
           'job_description' => 'required|string',
           //'start_date' => 'required|string',
           //'pay' => 'required|string',
           //'work_type' => 'required|string',

        ];
    }
}
