<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerformanceRequest extends FormRequest
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
            //'assignee_id' =>'required|integer',
            //'assigned_id' =>'required|string',
            'team_manager_id' =>'string',
            'hrms_project_id' =>'string',
            'team_leader' =>'required|string',
            'start_date' => 'required',
            'end_date' => 'nullable',
            'project_title' => 'required|string',
            'priority' => 'nullable|in:0,1,2',
            'description' => 'nullable|string',
            'sub_task' => 'nullable|string',
            'status' => 'nullable|in:0,1,2',
        ];
    }
}
