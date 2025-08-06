<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectTaskRequest extends FormRequest
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
            'name' =>'required|string',
            'description' => 'nullable',
            'min_project_id' => 'required|integer',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'assigned_id' => 'nullable|integer',
            'priority' => 'nullable|in:0,1,2,3,4',
            'status' => 'nullable|integer',
        ];
    }
}
